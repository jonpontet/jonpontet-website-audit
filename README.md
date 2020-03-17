Readme

`[jpwa_email]` Renders the email form

**Attributes**

* `id` Id of the form. (Used for anchors)

`[jpwa_form]` Renders the search form

**Attributes**

* `id` Id of the form. (Used for anchors)
* `title` Title to show above text field.
* `populate` Whether to auto-populate the value field with the current result's domain.

`[jpwa_result]` Renders the result

**Attributes**

None


Example email template

`
?>
Your Website Audit Report<br>
<br>
Speed<br>
Your score: {{category_speed_score_formatted}}<br>
<br>
Why did you get this score?<br>
I connected to Google PageSpeed Insights to analyse your website for speed, and this is how they score it based on their advanced criteria.<br>
<?php if ($categoriesMap['speed']->getScore() < 1): ?><a href="#">Find out how to make your website faster</a><br><?php endif ?>
<br>
Conversion<br>
Your score: {{category_conversion_score_formatted}}<br>
<br>
Why did you get this score?<br>
{{audit_presence-contact-form_tick}} A contact form was found on the page<br>
{{audit_presence-google-analytics_tick}} Google Analytics is installed<br>
<?php if ($categoriesMap['conversion']->getScore() < 1): ?><a href="#">Find out how to correct all these problems</a><br><?php endif ?>
<br>
<br>
SEO<br>
Your score: {{category_seo_score_formatted}}<br>
<br>
Why did you get this score?<br>
{{audit_robots-txt_tick}} Robots.txt is present<br>
{{audit_sitemap-xml_tick}} Sitemap.xml is present<br>
{{audit_meta-description_tick}} Meta description is correct<br>
{{audit_tag-h1_tick}} H1 tag is correct<br>
{{audit_tag-title_tick}} Title tag is correct<br>
{{audit_tag-title-length_tick}} Title length is correct<br>
{{audit_internal-links_tick}} Internal links is correct<br>
{{audit_external-links_tick}} External links is correct<br>
<?php if ($categoriesMap['seo']->getScore() < 1): ?><a href="#">Find out how to correct all these problems</a><br><?php endif ?>
<br>
<br>
Mobile Friendly<br>
Your score: {{category_mobile-friendly_score_formatted}}<br>
<br>
Why did you get this score?<br>
I connected to the Google Mobile-Friendly Test to analyse your website for mobile friendliness, and this is how they score it based on their advanced criteria.<br>
<?php if ($categoriesMap['mobile-friendly']->getScore() < 1): ?><a href="#">Find out how to make your website better on mobile</a><br><?php endif ?>
<br>
<br>
Social Media<br>
Your score: {{category_social-media_score_formatted}}<br>
<br>
Why did you get this score?<br>
{{audit_meta-facebook_tick}} All recommended Facebook tags are present<br>
{{audit_meta-twitter_tick}} All recommended Twitter tags are present<br>
<?php if ($categoriesMap['social-media']->getScore() < 1): ?><a href="#">Find out how to correct all these problems</a><br><?php endif ?>
<?php if (isset($categoriesMap['wordpress-security'])): ?>
<br>
<br>
WordPress Security<br>
Your score: {{category_wordpress-security_score_formatted}}<br>
<br>
Why did you get this score?<br>
{{audit_has-ssl_tick}} There is an SSL certificate<br>
{{audit_wp-login-404_tick}} Access to wp-login.php returns a 404 error<br>
{{audit_disabled-rest-user-scan_tick}} Unauthorised scans of users are blocked<br>
{{audit_unauthorised-load-scripts_tick}} Unauthorised access to load-scripts.php is blocked<br>
{{audit_unauthorised-load-styles_tick}} Unauthorised access to load-styles.php is blocked<br>
{{audit_disabled-pingbacks-and-trackbacks_tick}} Pingbacks and trackbacks are disabled for at least 1 blog post<br>
<?php if ($categoriesMap['wordpress-security']->getScore() < 1): ?><a href="#">Find out how to correct all these problems</a><br><?php endif ?>
<?php endif ?>
<br>
<br>
Accessibility<br>
Your score: {{category_accessibility_score_formatted}}<br>
<br>
Why did you get this score?<br>
I connected to Google PageSpeed Insights to analyse your website for accessibility, and this is how they score it based on their advanced criteria.<br>
<?php if ($categoriesMap['accessibility']->getScore() < 1): ?><a href="#">Find out how to make your website more accessible</a><br><?php endif ?>
`