# Store Hours plugin for Craft

This plugin adds a new “Store Hours” field type to Craft, for collecting the opening and closing hours of a business for each day of the week.

## Installation

To install Store Hours, follow these steps:

1.  Upload the storehours/ folder to your craft/plugins/ folder.
2.  Go to Settings > Plugins from your Craft control panel and enable the Store Hours plugin.

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
Sunday: 11:00 AM - 05:00 PM
Monday: 10:00 AM - 08:00 PM
Tuesday: 10:00 AM - 08:00 PM
Wednesday: 10:00 AM - 08:00 PM
Thursday: 10:00 AM - 08:00 PM
Friday: 10:00 AM - 08:00 PM
Saturday: 10:00 AM - 07:00 PM
```

## Changelog

### 1.1

- Updated to take advantage of new Craft 2.5 plugin features.

### 1.0

- Initial release
