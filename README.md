Store Hours for Craft CMS
===================

This plugin adds a new “Store Hours” field type to Craft, for collecting the opening and closing hours of a business for each day of the week. [Craft CMS](https://craftcms.com).

## Requirements

This plugin requires Craft CMS 3.0.0-beta.1 or later.


## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require craftcms/store-hours

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Store Hours.

4. You can now create a Store Hours field type under Settings → Fields.


## Template Rendering

```
{% set days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] %}

{% for dayHours in entry.storeHours %}
	<div>
		{{ days[loop.index0] }}: {{ dayHours.open|date('h:i a') }} - {{ dayHours.close|date('h:i a') }}
	</div>
{% endfor %}
```

Will output:

```
Sunday:    11:00 AM - 05:00 PM
Monday:    10:00 AM - 08:00 PM
Tuesday:   10:00 AM - 08:00 PM
Wednesday: 10:00 AM - 08:00 PM
Thursday:  10:00 AM - 08:00 PM
Friday:    10:00 AM - 08:00 PM
Saturday:  10:00 AM - 07:00 PM
```
