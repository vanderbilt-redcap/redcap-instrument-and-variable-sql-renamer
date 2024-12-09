<?php

namespace VUMC\REDCapInstrumentAndVariableSQLRenamer;

use REDCap;

class Autocomplete
{
    public static function replaceTerm($match)
    {
        $applyTag = function ($found) {
            // the sorrounding tag can be customized here
            $tagged = sprintf('<mark style="padding:0;background-color:yellow;""><b>%s</b></mark>', $found);
            return $tagged;
        };
        $found = @$match[0];
        if (!$found) {
            return '';
        }
        return $applyTag($found);
    }

    public static function getTermRegExp($terms)
    {
        $termsReducer = function ($carry, $term) {
            $quotedTerm = preg_quote($term); // we do not want to use regexps provided by the user interface
            $normalized = "($quotedTerm)"; // enclose in grouping parenthesis
            $carry[] = $normalized;
            return $carry;
        };
        $result = array_reduce($terms, $termsReducer, []);
        $regExp = sprintf('/%s/i', implode('|', $result));
        return $regExp;
    }

    public static function searchTerms($terms, $text)
    {
        $regExp = self::getTermRegExp($terms);
        $result = preg_replace_callback($regExp, 'self::replaceTerm', $text);
        return $result;
    }

    public static function sanatizingSearchTerms($getTerm, $type, $option)
    {
        // Santize search term passed in query string
        $search_term = trim(html_entity_decode(urldecode($getTerm), ENT_QUOTES));

        // Remove any commas to allow for better searching
        $search_term = str_replace(",", "", $search_term);

        // Return nothing if search term is blank
        if ($search_term == '') {
            exit('[]');
        }

        if ($type == "instrument" && $option == "new_var") {
            //Add form name on Format
            $search_term = preg_replace("/[^a-z_0-9]/", "", str_replace(" ", "_", strtolower($search_term)));
        }
        return $search_term;
    }

    public static function executeSQLSearch($module, $pid, $type, $option, $search_term)
    {
        // Set the subquery for all search terms used
        $subsqla = [];
        $subvalue = [];
        $subvalue[] = $pid;
        $subtype = "field_name";
        $sqlparams = ",form_name,element_label,field_order";
        if ($type == "instrument") {
            $subtype = "form_name";
            $sqlparams = "";
        }

        if ($option == "new_var") {
            $subsqla[] = $subtype . " = ?";
            $subvalue[] = $search_term;
        } else {
            // If search term contains a space, then assum multiple search terms that will be searched for independently
            if (strpos($search_term, " ") !== false) {
                $search_terms = explode(" ", $search_term);
            } else {
                $search_terms = array($search_term);
            }
            $search_terms = array_unique($search_terms);

            foreach ($search_terms as $key => $this_term) {
                // Trim and set to lower case
                $search_terms[$key] = $this_term = trim(strtolower($this_term));
                if ($this_term == '') {
                    unset($search_terms[$key]);
                } else {
                    $subsqla[] = $subtype . " like ?";
                    $subvalue[] = "%" . $this_term . "%";
                }
            }
        }

        $subsql = implode(" or ", $subsqla);
        $sql = "SELECT DISTINCT $subtype $sqlparams
					FROM redcap_metadata 
					WHERE project_id = ? AND ($subsql) 
					ORDER BY form_name";
        $q = $module->query($sql, $subvalue);
        return [$q, $subtype, $this_term, $search_terms];
    }

    public static function processResults($q, $option, $type, $this_term, $search_terms, $subtype)
    {
        while ($row = $q->fetch_assoc()) {
            if ($option == "new_var") {
                $results[$key] = array('value' => $row[$subtype], 'label' => '', 'info' => '', 'group' => '');
            } else {
                if ($type == "instrument" || ($type == "variable" && $row["field_name"] != $row['form_name'] . "_complete" && $row['field_order'] != "1")) {
                    // Trim all, just in case
                    $label = trim(strtolower($row[$subtype]));
                    $info = "";
                    $group = "";
                    if ($type == "variable") {
                        $info = htmlspecialchars(trim($row['element_label']));
                        $group = REDCap::getInstrumentNames(trim(strtolower($row['form_name'])));
                    } else {
                        $label = REDCap::getInstrumentNames(trim(strtolower($row[$subtype])));
                    }
                    // Calculate search match score.
                    $userMatchScore[$key] = 0;

                    // Loop through each search term for this person

                    // Set length of this search string
                    $this_term_len = strlen($this_term);
                    // For partial matches, give +1 point for each letter
                    if (strpos($row[$subtype], $this_term) !== false) {
                        $userMatchScore[$key] = $userMatchScore[$key] + $this_term_len;
                    }

                    // Wrap any occurrence of search term in label with a tag
                    $label = self::searchTerms($search_terms, $label);

                    // Add to arrays
                    $results[$key] = array(
                        'value' => $row[$subtype],
                        'label' => $label,
                        'info' => $info,
                        'group' => $group
                    );
                    $usernamesOnly[$key] = $row['username'];
                    // If username, first name, or last name match EXACTLY, do a +100 on score.
                    if (in_array($row[$subtype], $search_terms)) {
                        $userMatchScore[$key] = $userMatchScore[$key] + 100;
                    }

                    // Increment key
                    $key++;
                }
            }
        }

        // Sort users by score, then by username
        if ($option == "old_var" && $results != null) {
            $count_users = count($results);
            if ($count_users > 0) {
                // Sort
                array_multisort($userMatchScore, SORT_NUMERIC, SORT_DESC, $usernamesOnly, SORT_STRING, $results);
                // Limit only to X users to return
                $limit_users = 10;
                if ($count_users > $limit_users) {
                    $results = array_slice($results, 0, $limit_users);
                }
            }
        }
        return json_encode($results);
    }

    public static function getAutocompleteData($module, $pid, $getTerm, $type, $option)
    {
        $search_term = self::sanatizingSearchTerms($getTerm, $type, $option);

        $sqlData = self::executeSQLSearch($module, $pid, $type, $option, $search_term);
        $q = $sqlData[0];
        $subtype = $sqlData[1];
        $this_term = $sqlData[2];
        $search_terms = $sqlData[3];

        return self::processResults($q, $option, $type, $this_term, $search_terms, $subtype);
    }
}
