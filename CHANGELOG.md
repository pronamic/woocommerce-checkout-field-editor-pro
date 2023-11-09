#### 3.5.0 (29-09-2023)
IMPROVED: Added WooCommerce 8.1 compatibility.
IMPROVED: Added WordPress 6.3 compatibility.
#### 3.5.1 (09-06-2023)
IMPROVED: Added compatibility with HPOS(High-Performance order storage).
IMPROVED: Added WooCommerce 7.7 compatibility.
IMPROVED: Added WordPress 6.2 compatibility.

#### 3.5.0 (31-03-2023)
FIX: Fixed the disabled time issue with the linked date in the time picker field.
FIX: Fixed the compatibility issue with WooCommerce PayPal Payments plugin.
FIX: Fixed the compatibility issue with the WP Event Manager plugin.
IMPROVED: Added compatibility for FOX – Currency Switcher Professional for WooCommerce plugin.
IMPROVED: Improved the working of a maximum selection property for multi-select fields.
IMPROVED: Added Woocommerce 7.5 compatibility.
IMPROVED: Added WordPress 6.1 compatibility.
NEW FEATURE: Added instant validation on the checkout page.

#### 3.4.0 (25-08-2022)
FIX: Fixed the address format override issues of conditionally displayed fields.
IMPROVED: Added Woocommerce 6.8.2  compatibility.
IMPROVED: Added a new filter to upload the file from the admin order page.
IMPROVED: Improved the file upload field type functionality.
NEW FEATURE: Added a custom file upload button property for the File upload field type.
NEW FEATURE: Show alert on selecting display position of sections as before/after terms and conditions.

#### 3.3.0 (02-06-2022)
IMPROVED: Added WooCommerce 6.5 compatibility.
IMPROVED: Added WordPress 6.0 compatibility.
NEW FEATURE: Added input masking feature for text and telephone field type.
NEW FEATURE: Added conditional rule based on shipping class.
NEW FEATURE: Added conditional rule based on shipping weight.
NEW FEATURE: Added conditional rule based on product type.
NEW FEATURE: Added a new feature to disable the time slot for time picker field type.

#### 3.2.0 (27-04-2022)
IMPROVED: Conditional Rules.
IMPROVED: Jquery Datepicker mindate and maxdate if server and user are in different time zone.
IMPROVED: Added WooCommerce 6.4.1 compatibility.
NEW FEATURE: Added address autofill feature on the checkout page and my account page.
NEW FEATURE: Added option to disable select2 property specifically for each select and multi-select field.

#### 3.1.9 (16-03-2022)
FIX: Fixed the issue of not displaying fields in the admin emails.
IMPROVED: Retain the options while changing the field type.
IMPROVED: Added WooCommerce 6.3.1 compatibility.
IMPROVED: Added WordPress 5.9 compatibility.
NEW FEATURE: Added new field type "Datetime local".
NEW FEATURE: Added new field type "Date".
NEW FEATURE: Added new field type "Month".
NEW FEATURE: Added new field type "Time".
NEW FEATURE: Added new field type "Week".
NEW FEATURE: Added new field type "URL".
NEW FEATURE: Added new field type "Paragraph".

#### 3.1.8 (28-12-2021)
FIX: Fixed tag-related display rule issues.
FIX: Fixed the issue of the field label not displaying for the validation error message in custom sections.
IMPROVED: Added new filters thwcfe_custom_validator_pattern, thwcfe_confirm_validator_pattern, thwcfe_field_value_length_calculation_function, thwcfe_mb_strlen_encoding & thwcfe_hidden_fields_display_position.
IMPROVED: Added RTL support with WordPress.
IMPROVED: Added WooCommerce 6.0.0 compatibility.

#### 3.1.7 (06-12-2021)
FIX: Removed Display rule based on Product Variations.
FIX: Fixed the issue of conditional rule with the shipping method for check box field.
FIX: Fixed the issue of the 'Show section title field functionality.
FIX: Fixed the issue of the Max length validation error displayed for the multi-select field.
FIX: Fixed the issue of accepting value as an HTML tag for the default value of the hidden field.
FIX: Index params added in thwcfe_uploaded_file_name filter.
FIX: Jquery UI files served locally instead of CDN.
FIX: Fixed the issue with Cart only containing tag, product, categories, product variation functionality.
FIX: Handled the case of zero values in Custom validation.
FIX: Fixed checkout field display issues in multisite.
IMPROVED: Added minimum length option for Text field.
IMPROVED: The default value of price type is set as fixed.
IMPROVED: The default value of the Number field will only take numeric values.
IMPROVED: Time changes with respect to the WordPress time for the Time picker field.
IMPROVED: Added Conditional rule based on All tags.
IMPROVED: All update_option calls are updated with autoload and no parameter with filter thwcfe_option_autoload.
IMPROVED: Custom billing or shipping field data gets displayed in the corresponding section.
IMPROVED: Added new filters use_custom_ship_to_different_address_css_selector, ship_to_different_address_css_selector, thwcfe_modify_order_posts_custom_column.
IMPROVED: Added PHP 8 compatibility.
IMPROVED: Added WooCommerce 5.9.0 compatibility.
IMPROVED: Added Wordpress 5.8 compatibility.

#### 3.1.6 (11-05-2021)
FIX - Fix for a disabled date or disabled day is selected as the default date for date picker field.
FIX - Fix for unexpected warning displayed while single quotes added to input value at display rules tab
FIX - Fix for autofill of field doesn't validate the maximum length value.
FIX - Fix for '-' symbol in product title cause HTML Unicode symbol in the select2 field.
FIX - Fixed display rule of the field - Day not equals or Date does not equal condition based on date picker field not working on initial page load.
FIX - Fixed inconsistent validation message of conditionally displayed fields at My Account page >> Edit address.
FIX - Fixed file upload field issues.
FIX - Fixed conditional field visibility issues on my account page.
FIX: WooSelect - enhanced select added in my account pages.
IMPROVEMENT - Option to add hours parameter also for min, max & default days like +1d +12h etc.
IMPROVEMENT - Variable products (product variation) support added in Repeat rule's product dropdown.
IMPROVEMENT - Variable products (product variation) included in display rule's product dropdown.
IMPROVEMENT - UI improvements.
IMPROVEMENT - Maximum length PHP validation added for field.
IMPROVEMENT: Repeated rule functionality on default checkout field improved.
IMPROVEMENT: Overriding default sections by creating a section with same name is blocked.
NEW FEATURE - Option to force use server date time as date picker reference using filter (thwcfe_force_wp_date_time_for_date_picker).
NEW FEATURE - New filter to modify Checkout field HTML (thwcfe_field_html).
NEW FEATURE: Warning added before creating a new field with an existing field name.

#### 3.1.5 (31-01-2021)
IMPROVEMENT: File upload functionality improvements.
IMPROVEMENT: WooCommerce latest version compatibility added.

#### 3.1.4 (24-12-2020)
FIX: Fixed the issue of users can't view the preview of the uploaded file on the checkout page &amp; My Account page when a file upload field is set as user meta.
FIX: Fixed the issue of users can't delete the uploaded file on the checkout page &amp; My Account page when multiple files are uploaded in a single file upload field.
FIX: Fixed the jQuery UI i18n JS file missing error in the checkout page.
FIX: Fixed the issue of not able to select options from the Select2 field on the checkout page.
IMPROVEMENT: Improved UI dialog button styles in settings screen.
IMPROVEMENT: License manager updated.
IMPROVEMENT: WordPress latest version compatibility added.
IMPROVEMENT: WooCommerce latest version compatibility added.

#### 3.1.3 (11-11-2020)
IMPROVEMENT: Repeat rules functionality improvements.

#### 3.1.2 (03-10-2020)
IMPROVEMENT: File Upload field to support multiple file upload added.
IMPROVEMENT: Added options to enable/disable address fields properties override with locale settings.

#### 3.1.1 (27-07-2020)
IMPROVEMENT: Improved the price calculation process to eliminate the chance of missing extra prices when slow ajax calls performed.
IMPROVEMENT: WooCommerce latest version compatibility added.

#### 3.1.0 (19-06-2020)
FIX: Fix for the issue of missing ‘Additional Fields’ section when upgrading from free to premium.
IMPROVEMENT: Improved the price calculation process to eliminate the chance of missing extra prices when slow ajax calls performed.
IMPROVEMENT: Removed the unused dropdown ‘Validation’ from the settings popup form for field types 'Select', 'Radio' and 'Checkbox'.

#### 3.0.9 (07-05-2020)
FIX: Fix for the display rule issue when settings conditions based on Tag &amp; Cart Only Contains.
FIX: Fix for the display rule issue when settings custom shipping fields based conditions.
IMPROVEMENT: Zapier latest version 2.0.x compatibility added.

#### 3.0.8 (15-04-2020)
FIX: Fix for the issue of not setting custom fields price when user creating account from checkout page.
FIX: Fix for the issue of not hiding section title in order details pages even if no fields are available to display.
IMPROVEMENT: Settings screen style improvement.

#### 3.0.7 (26-03-2020)
IMPROVEMENT: WooCommerce latest version compatibility added.

#### 3.0.6 (18.10.2019)
FIX: Fix for the issue of displaying conditionally hidden non input fields in order details pages.
FIX: Fix for the issue of not opening section edit popup when special characters provided for the title.
IMPROVED: License manager updated
IMPROVED: New filter to modify plugin access capability.
NEW FEATURE: New filter to modify custom fields display position in pdf invoices &amp; packaging slips.

#### 3.0.5 (26.09.2019)
FIX: Fixed the issue of not showing save button in field settings popup when more options added to select field after changed the popup position.
FIX: Fixed the issue of not displaying additional section fields value in admin order details page.
FIX: Fixed the issue of not displaying custom fields in the emails which sending on changing order status from the order details page.
IMPROVED: Lazy loading products to improve product list loading time.
NEW FEATURE: Added option to set product tags based conditional rules.

#### 3.0.4 (19.09.2019)
FIX: Fix for the issue of not showing placeholder for country and state fields.
FIX: Fix for the issue of not adding extra price when slow network delayed the Ajax calls.
FIX: Fixed the issue of displaying custom field values twice in order emails.
FIX: Fixed the issue of displaying custom fields in pay order page.
FIX: Fixed the file upload issue in the review panel.
IMPROVED: Added filters to enable or disable date-picker controls.
IMPROVED: Displaying section title in order details pages and order emails.
IMPROVED: Displaying option price along with option text.
NEW FEATURE: New option added to repeat fields and sections based on product quantity.
NEW FEATURE: New conditional rule options added (Starts with, not starts with, regex).

#### 3.0.3 (03.06.2019)
FIX: Fixed the conditional rule issue of address fields with WooCommerce latest version.
IMPROVED: Added the option to display headers &amp; Labels in emails.

#### 3.0.2 (31.05.2019)
FIX: Sections sorting issue in order details page fixed.
FIX: WooCommerce Zapier latest version compatibility issue fixed.
FIX: Added fix for the fatal error when WooCommerce session object is not available.

#### 3.0.1 (13.05.2019)
FIX: WooCommerce Zapier plugin compatibility issue fixed.
FIX: Fix for the issue of not deleting uploaded file from My Account page.

#### 3.0.0 (26.04.2019)
FIX: Fix for the issue of not translating field values in PDF invoices.
FIX: Fix for the issue of not translating option text in emails.
FIX: Fix for 'value in' conditional rule issue with empty selection
IMPROVED: New Ajax conditional rules 'contains' and 'not contains' added.
IMPROVED: Added thumbnail view for file uploads.
IMPROVED: File upload preview added.
NEW FEATURE: Option to include custom fields in WooCommerce API response.

#### 2.9.9 (21.02.2019)
FIX: Fix for the issue of clearing default value when conditions applied.
IMPROVED: New license manager implemented.

#### 2.9.8 (07.12.2018)
FIX: Fix for the conflict issue with Themehigh's Multi-step Checkout plugin.
IMPROVED: New filter to disable product drop-down in conditions settings.
IMPROVED: Added usermeta support for file upload fields.
IMPROVED: Custom field additional price calculation improved.

#### 2.9.7 (12.11.2018)
FIX: Fix for the issue of not working price with File upload field type.
FIX: Fix for default fields display order issue.
FIX: Fix for Conditional rules settings action buttons missing issue.
NEW FEATURE: New filter to modify field value.

#### 2.9.6 (26.10.2018)
FIX: Fix for the issue of not accepting double quotes for field label and description.
FIX: Fix for the issue of not working update cart properly.
FIX: Fix for duplicate category list issue when validating conditions.
FIX: Fix for the style issues with the latest WooCommerce version.
NEW FEATURE: New filter to modify default sections.

#### 2.9.5 (15.08.2018)
FIX: Fix for the issue of displaying empty space in review order section.
FIX: Fix for the issue of not working payment method based conditions.
FIX: Fix for date-picker browser translation issue.
FIX: Fix for the issue of not working mandatory field validation for default fields.
FIX: Fix for the price calculation issue for checkbox group.
IMPROVED: ThemeHigh's WooCommerce Multi-Step Checkout plugin support added.
IMPROVED: Automatically set min and max date for date-picker based on the linked date picker values.
IMPROVED: Disabling dates from date-picker those are already selected in the linked date-picker
NEW FEATURE: Added helper functions to use with third party functions.
NEW FEATURE: Added new action hooks before and after custom section.

#### 2.9.4 (25.05.2018)
FIX: Fix for the display price compatibility issue with WooCommerce older version.
FIX: Fix for the license activation issue.
FIX: Fix for the issue of not working date string based conditional rules.
FIX: Fix for the issue of not setting the default value for the country field.
FIX: Fix for the address field required validation issue.
FIX: Fix for the issue of calculating extra cost on conditionally hidden shipping fields.
IMPROVED: Added option to display option text instead of option value in order emails, thank you page and order details page.
IMPROVED: Changed default value field form text to textarea for textarea fields. 
IMPROVED: Added off value for checkbox fields.
NEW FEATURE: Added option to duplicate section.
NEW FEATURE: Implemented conditional rules for sections.
NEW FEATURE: Added new field types File, Telephone, Email and Number.
NEW FEATURE: Added filter to modify quantity for dynamic price option.
NEW FEATURE: Added filter to modify field description display position.
NEW FEATURE: Added filter to skip address fields override with locale.
NEW FEATURE: Added filter to support multi step checkout
NEW FEATURE: Added new positions in review order table.
NEW FEATURE: Added hook to modify field options.

#### 2.9.3 (02.03.2018)
FIX: Fix for the issue of not working CSV export with 'multi row per order' option.
FIX: Fix for single quot issue in section edit form. 
FIX: Fix for the issue of validating conditionally hidden fields in my account page.
FIX: Fix for section title display issue in my account page. 
IMPROVED: Added option to change start day of a Date picker.
IMPROVED: Added option to limit max selections allowed for multi select.
IMPROVED: Displaying tax label in order review section in checkout page.
IMPROVED: Added confirmation check before deleting a custom section.
IMPROVED: Added confirmation check before reseting advance settings.
IMPROVED: Show field name along with field label in field selection drop-down in conditional rules settings tab.
IMPROVED: Added filter to show only custom fields in admin new order custom fields section.
IMPROVED: Added option to choose number of checkbox options to display per line for checkbox group.
NEW FEATURE: ThemeHigh's WooCommerce Multi Step Checkout plugin support added.

#### 2.9.2 (01.02.2018)
FIX: Fix for the compatibility issue with the latest version of 'WooCommerce Customer / Order CSV Export' plugin.
FIX: Fix for the issue of displaying conditionally hidden fields in order details pages.
FIX: Fix for the issue of validating non user meta fields in my account page.
FIX: Fix for the issue of not triggering webhooks with the latest version of WooCommerce.
FIX: Fix for the compatibility issue with the latest version of 'WooCommerce Zapier' plugin
FIX: Fix for WPML compatibility issue with category.
FIX: Fix for the issue of not displaying default values for the fields which displayed using before &amp; after customer details hooks.
IMPROVED: Displaying subtitles in order detail pages.
NEW FEATURE: Added filter to display hidden fields as text fields in my account page.
 
#### 2.9.1 (22.12.2017)
FIX: Fix for the issue of not calculating field price for the fields displayed in order review section.
FIX: Fix for the field priority setting issue.
FIX: Fix for time-picker cross day time slots issue.
FIX: Fix for not working user role based conditional rules in my account page.
FIX: Fix for not showing section titles in my account page.
FIX: Fix for the issue of validating conditionally hidden mandatory fields kin my account page.
IMPROVED: Allowing uppercase letters in field &amp; section names.
IMPROVED: Touch keyboard disabled for select fields in phone browsers.
IMPROVED: Improved custom validation function for field types radio, check-box, select etc.
NEW FEATURE: Added filter to ignore row split in my account page fields.
NEW FEATURE: Added conditional rules based on product variations.
NEW FEATURE: Added filter to set field as read-only.
NEW FEATURE: Added filter to include additional fields in fields based conditional rules.

#### 2.9.0 (17.11.2017)
FIX: Fix for the issue of not displaying Time picker properly in My Account page.
FIX: Fix for undefined index issue in invoices
FIX: Fix for not working conditional fields properly in My Account page.
FIX: Fix for not working all fields types properly in My Account page.
IMPROVED: Removed disabled fields from conditional rule settings page.

#### 2.8.9 (08.11.2017)
FIX: Fix for the issue of not displaying textarea value with linebreaks.
FIX: Fix for the issue of invalid request error when activating license.
IMPROVED: Improved to work conditional rules in My Account page.
IMPROVED: Improved to display all fields types in My Account page with respective type features.

#### 2.8.8 (27.10.2017)
FIX: Fix for the issue of not preloading user meta fields in custom sections.
FIX: Fix for the issue of not validating conditions properly when "all categories" or "all products" chosen.

#### 2.8.7 (20.10.2017)
IMPROVED: New filters added to control field display in My account edit address form.
IMPROVED: Added the option to choose validation for password field.
FIX: Fix for the price calculation issue of hidden fields type.
FIX: Fix for the issue of displaying heading &amp; label even when disabled.

#### 2.8.6 (13.10.2017)
IMPROVED: Compatibility with WooCommerce version 3.2.0
FIX: Replaced removed/deprecated hooks.

#### 2.8.5 (12.10.2017)
FIX: Fix for the multi-select issue in admin form
FIX: Fix for the fatal error "Can't use method return value in write context".

#### 2.8.4 (06.10.2017)
IMPROVED: Address field format override to includes default email and phone fields.

#### 2.8.3 (04.10.2017)
FIX: Removed multiple events added for conditional field change listener.
FIX: Fix for the issue of not displaying custom fields in emails when re-sending from Order details page.
FIX: Fix for the issue of not working required property for address fields.

#### 2.8.2 (01.10.2017)
NEW FEATURE: Added new filters to modify display rule of fields.
NEW FEATURE: Added new filters to modify min date, max date, disabledDays, disabledDate etc for date-picker.
FIX: Fix for the copying settings from free version to premium issue.

#### 2.8.1 (29.09.2017)
IMPROVED: Structural changes and performance improvement.
IMPROVED: Optimized the field properties to fix memory issue.

#### 2.8.0 (30.08.2017)
NEW FEATURE: Added the option to display heading &amp; label in Thank you page and order details page.
IMPROVED: Added the option to link date-picker and time-picker fields.
FIX: Fix for the issue of refreshing order summary for conditional price fields.
FIX: Fix for the issue of checkbox checked flag conflict with pail poet.
FIX: Fix for the issue of sending duplicate Ajax requests.

#### 2.7.9 (31.07.2017)
IMPROVED: New filter added to make the date picker field editable..
IMPROVED: API Key text changed to License Key.
FIX: Fix for the default address fields required validation issue.
FIX: Fix for the issue while saving field values in My Account field edit form.
FIX: Fix for the issue of not hiding disabled fields properly.
FIX: Fix for multi-select field z-index issue in field settings popup form.

#### 2.7.8 (11.07.2017)
NEW FEATURE: Dynamic &amp; custom price options added.
NEW FEATURE: Added dynamic price calculation based on other field value.
NEW FEATURE: Added new filters to modify the extra cost.
NEW FEATURE: Added conditions based on date and day.
NEW FEATURE: Added the option to backup plugin settings.
IMPROVED: Hide extra cost if zero.
IMPROVED: Label field display rule improved for empty labels.
IMPROVED: WPML taxonomy translation support added for category based conditions.
IMPROVED: Validating and filtering fields before adding extra price.
IMPROVED: Div wrapper added for custom sections.
FIX: Fix for the issue of not displaying conditional fields in My Account page.
FIX: Fix for the issue of not displaying billing address fields in delivery address when ship to billing address enabled.
FIX: Fix for the issue of not translating field label in validation message.

#### 2.7.7 (26.04.2017)
NEW FEATURE: Confirm field validation feature added.
NEW FEATURE: Field options sort feature added for the field types select &amp; radio in settings popup form.
IMPROVED: Added option to set wrapper class for 'Heading' and 'Label' field types.
FIX: Fix for checkbox group value display issue in My Account order details page.
FIX: Backward compatibility added for get tax status function.
FIX: Fix for field display order caused by priority sort.

#### 2.7.6 (19.04.2017)
NEW FEATURE: Added taxable option for price fields.
FIX: Fix for address format override &amp; address display issue.
FIX: Backward compatibility added for WooCommerce 3.0.
FIX: Fix for the performance issue while loading 1000+ products in settings page.
FIX: Fix for Title overlap issue.

#### 2.7.5 (05.04.2017)
IMPROVED: WooCommerce 3.0 Compatible.
FIX: Fix for address format override issue.
FIX: Fix for the issue of not hiding default customer details('customer_note', 'billing_email', 'billing_phone') in emails when choose to hide.

#### 2.7.4 (20.03.2017)
FIX: Fix for start time display issue in time picker.
FIX: Fix for custom select field display issue in admin profile view.
FIX: Fix for settings page broken issue when RTL language selected.

#### 2.7.3 (23.02.2017)
NEW FEATURE: WooCommerce PDF Invoices &amp; Packing Slips plugin can now display custom fields.
NEW FEATURE: Added option to display character count for input fields.
IMPROVED: Changed the address format settings field from text to textarea.
IMPROVED: More display positions are added for custom sections.

#### 2.7.2 (09.02.2017)
NEW FEATURE: Included option to add role based conditional fields.

#### 2.7.1 (07.02.2017)
FIX: Fix for multi-select field issue in settings page.

#### 2.7.0 (01.02.2017)
NEW FEATURE: Added new settings to display custom fields as part of default address fields.
IMPROVED: Added new default validation options Postcode and State.
IMPROVED: Custom user fields are now available for editing in admin view profile page.

#### 2.6.9 (23.01.2017)
NEW FEATURE: Added more options for cart total related conditional rules.
FIX: Fix for showing non user meta fields in My Account &gt; Edit Address form.
FIX: Fix for field/section subtitle inline style issue.
FIX: Fix for the issue total based conditional rules were not working with 0 value.

#### 2.6.8 (14.01.2017)
NEW FEATURE: Added support to display custom fields for WooCommerce Webhooks.
FIX: Fix for the issue of showing forward slashes while using html content for label.
FIX: Fix for the issue of skipping validations when using 0 as value.

#### 2.6.7 (11.12.2016)
IMPROVED: Added new settings to display fields in admin and customer emails and order pages separately.
FIX: Fix for translation bug - WPML

#### 2.6.6 (07.11.2016)
FIX: Fix for the issue Time picker start time is not working properly when the current time is earlier than the Min. Time

#### 2.6.5 (21.10.2016)
FIX: Fix for the issue not updating total properly when switching between price field options.
FIX: Fix for the issue not listing custom field names when scanning plugin with WPML.

#### 2.6.4 (14.10.2016)
NEW FEATURE: Included an option to add CSS classes to field wrapper element.
FIX: Shop Order column filter bug fixes.

#### 2.6.3 (06.10.2016)
FIX: Zappier fields not available to map issue fixed.
FIX: Checkbox group price issue fixed.

#### 2.6.2 (25.09.2016)
NEW FEATURE: Added new field checkbox group.
NEW FEATURE: Added few advance settings to handle few common issues like as memory issue.
NEW FEATURE: Included an option to display custom fields in shop order columns.

#### 2.6.1 (08.09.2016)
NEW FEATURE: Included a new attribute to add field Description.
NEW FEATURE: Add an option to display field name in custom validation messages.

#### 2.6.0 (02.09.2016)
NEW FEATURE: Included new feature to add User Meta fields into checkout page.
NEW FEATURE: Included new feature to create custom validators.

#### 2.5.2 (03.08.2016)
FIX: Zappier fields not available to map issue fixed.
NEW FEATURE: Included an option to add max length validator to input fields.

#### 2.5.1 (22.07.2016)
NEW FEATURE: Added WooCommerce Customer / Order CSV Export plugin support, and now custom fields can exported.
FIX: Multiple fixes to string translation using WPML.
NEW FEATURE: Added support to Zapier integration plugin.

#### 2.5.0 (13.07.2016)
NEW FEATURE: Included new feature to conditionally display fields in checkout page based on other checkout fields value.
NEW FEATURE: Added string translation support using WPML.

#### 2.4.2 (06.06.2016)
NEW FEATURE: Added a new attribute 'Start Time' to TimePicker field which is used to hide timeslots which is prior to a specific time from now(for example allow only those time slots which is after 3 hour and 30 minutes from now).

#### 2.4.1 (28.05.2016)
NEW FEATURE: Added an option to disable certain days in DatePicker. Ex: disable all Saturdays and Sundays from the calendar popup.
NEW FEATURE: Added an option to disable certain dates in DatePicker. Ex: disable 2016-12-25 from the calendar popup.

#### 2.4.0 (13.05.2016)
NEW FEATURE: New feature to add price fields (add an extra cost to cart total based on a field selection).
FIX: Fix for showing error message when adding select options.

#### 2.3.1 (30.04.2016)
FIX: Added fix for the issue not showing select when options have special characters.
FIX: Added fix for DatePicker date format issue.

#### 2.3.0 (22.04.2016)
NEW FEATURE: Included new feature to add conditional fields.
NEW FEATURE: Included option to add 'Hidden' fields.
IMPROVED: Select, multiselect and radio improved to accept key value pair as options.

#### 2.2.2 (26.02.2016)
NEW FEATURE: Added an option to set Time Format for Time Picker.
NEW FEATURE: Added i18n support for Time Picker.

#### 2.2.1 (24.02.2016)
FIX: Loading language file from WordPress language folder issue resolved.
FIX: Debug mode warning message resolved.

#### 2.2.0 (18.01.2016)
NEW FEATURE: Added three new field types(Time Picker, Hidden Field and Label).
NEW FEATURE: Added more options to customize Date Picker properties.
NEW FEATURE: Added multilingual support.

#### 2.1.3 (08.12.2015)
FIX: Fix for disabled field validation issue in custom sections.
FIX: Fix for ID/Name validation issue.
FIX: Fix for multiselect value not displaying issue in order email.

#### 2.1.2 (04.12.2015)
FIX: Fix for the issue in displaying custom section(s) after Order Note.

#### 2.1.1 (25.11.2015)
FIX: Fix for remove section issue when using a numeric as section name.
FIX: Fix for shipping fields modification not reflecting issue.

#### 2.1.0 (04.11.2015)
NEW FEATURE: Added an option to add new section in checkout page.
NEW FEATURE: Added an option to edit newly added section(s).
NEW FEATURE: Added an option to delete newly added section(s).

#### 2.0.2 (31.10.2015)
FIX: Change checkout field order JavaScript conflict workaround added.
FIX: Display custom fields in order review issue fix.

#### 2.0.1 (26.10.2015)
NEW FEATURE: Added an option to reset all custom changes back to the original WooCommerce fields set using a button ‘Reset to default fields’.
NEW FEATURE: Added an option to enable/disable field(s)(temporarily remove) from displaying in checkout page, order details page and emails.

#### 2.0.0 (19.10.2015)
INITIAL RELEASE: WooCommerce Checkout Form Designer pro initial version.
INITIAL RELEASE: Feature to add new checkout fields in billing, shipping and additional fields sections.
INITIAL RELEASE: Feature to edit checkout field properties in billing, shipping and additional fields sections.
INITIAL RELEASE: Remove checkout fields from billing, shipping and additional fields sections.
INITIAL RELEASE: Change the display order of checkout fields.
INITIAL RELEASE: Option to add or remove fields from displaying in order details page.
INITIAL RELEASE: Option to add or remove fields from displaying in order confirmation email.