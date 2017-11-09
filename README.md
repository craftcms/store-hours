# Store Hours plugin for Craft

This plugin adds a new “Store Hours” field type to Craft, for collecting the opening and closing hours of a business for each day of the week.

## Installation

To install Store Hours, follow these steps:

1.  Upload the storehours/ folder to your craft/plugins/ folder.
2.  Go to Settings > Plugins from your Craft control panel and enable the Store Hours plugin.

## Template Rendering

```twig
{% set days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] %}

{% for dayHours in entry.storeHours %}
    <li>
		{{- days[loop.index0] }}: {{ dayHours.open|date('h:i a') }} - {{ dayHours.close|date('h:i a') -}}
	</li>
    </p>
{% endfor %}
```

Will output:

```html
<li>Sunday: 11:00 AM - 05:00 PM</li>
<li>Monday: 10:00 AM - 08:00 PM</li>
<li>Tuesday: 10:00 AM - 08:00 PM</li>
<li>Wednesday: 10:00 AM - 08:00 PM</li>
<li>Thursday: 10:00 AM - 08:00 PM</li>
<li>Friday: 10:00 AM - 08:00 PM</li>
<li>Saturday: 10:00 AM - 07:00 PM</li>
```
