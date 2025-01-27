# redcap-instrument-and-variable-sql-renamer
<h2 style='color: #33B9FF;'>Description</h2>
This module allows <b>super users</b> to change an Instrument or Variable name preserving all information previously associated with it, modifying all data and metadata associated to the old name, includding the branching logic.

<span style="color:red">
*Other features such as alerts, locking, field comments, etc. will not be modified.
</span>
<br/>
<span style="color:red">
*Primary Key variables cannot be renamed.
</span>
<h2 style='color: #33B9FF;'>Installation</h2>
<span style="float:left;padding-right:5px;">When enabling the module, a new link </span> <img src="/docs/readme_img_1.png" width="100" style="float: left;" alt="This picture shows the module's configuration link in the External Modules section"> <span style="float:left;padding-left:5px;">will show up on the External Modules section.</span>
<br/><br/>
When clicking the link, the module's page will load:

<img src="/docs/readme_img_2.png" alt="This picture shows the module's main page">

<h2 style='color: #33B9FF;'>Usage</h2>
To use the tool simply follow these steps:

1. Select which type of data you want to modify (variable or instrument).<br/><img src="/docs/readme_img_3.png" width="250" alt="This picture shows the data type to modify">
2. Select the name. You can use the dropdown or type the name and find one.<br/><img src="/docs/readme_img_4.png" width="250" alt="This picture shows the data dropdown">
3. Upon selecting the variable/instrument, a new input will appear. Add the new name.<br/><img src="/docs/readme_img_5.png" width="350" alt="This picture shows the new input">
4. For Instruments add the Form Name as it would show on REDCap's Designer page.<br/><img src="/docs/readme_img_6.png" width="600" alt="This picture shows the Instrument Names as they appear on the Designer page.">
5. If a name already exists an error message will show.<br/><img src="/docs/readme_img_7.png" width="400" alt="This picture shows the error when trying to add an existing name">
6. For Instruments a warning message will alert you before continuing.<br/><img src="/docs/readme_img_8.png" width="400" alt="This picture shows the popup message">
7. After Confirming the changes, a success message will appear.<br/><img src="/docs/readme_img_9.png" alt="This picture shows the instrument success message">
8. You can check the changes by click on the dropdown or going to REDCap's Designer Page.<br/><img src="/docs/readme_img_10.png" width="250" alt="This picture shows the instrument dropdown updated">
9. You can also check REDCap's Logging to see what user did the changes and what changes were made. <br/><img src="/docs/readme_img_11.png" alt="This picture shows the logs showing the changes">

<h2 style='color: #33B9FF;'>Developer Section: SQL Tables Involved</h2>
All SQL queries are done with transactions, this means that, if something fails, it will revert back and no changes will be applied.
<br/>
The logs show all tables involved but here's a summary:

- Instruments
    - redcap_data: field_name 
    - redcap_metadata: form_name 
    - redcap_metadata: field_name 
    - redcap_metadata: form_menu_description 
    - redcap_surveys: form_name
    - redcap_surveys: title
    - redcap_events_forms: form_name
- Variables
    - redcap_data: field_name 
    - redcap_metadata: field_name
    - redcap_metadata: branching_logic
  
<span style="color:red">
*There are up to 5 redcap_data tables (redcap_data1 to redcap_data5). The logs will display which table has been affected.
</span>