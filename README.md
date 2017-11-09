Store Hours for Craft CMS
===================

This plugin adds a new “Store Hours” field type to Craft, for collecting the opening and closing hours of a business for each day of the week.

## Requirements

This plugin requires Craft CMS 3.0.0-beta.20 or later.


## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require craftcms/store-hours

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Store Hours.

4. You can now create a Store Hours field type under Settings → Fields.


## Template Rendering

```twig
{% set days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] %}

{% for dayHours in entry.storeHours %}
    <li>
        {{- days[loop.index0] }}: {{ dayHours.open|date('h:i a') }} - {{ dayHours.close|date('h:i a') -}}
    </li>
{% endfor %}
```

Will output:

```html
<li>Sunday: 11:00 am - 05:00 pm</li>
<li>Monday: 10:00 am - 08:00 pm</li>
<li>Tuesday: 10:00 am - 08:00 pm</li>
<li>Wednesday: 10:00 am - 08:00 pm</li>
<li>Thursday: 10:00 am - 08:00 pm</li>
<li>Friday: 10:00 am - 08:00 pm</li>
<li>Saturday: 10:00 am - 07:00 pm</li>
```

If you want to show Monday’s hours first, do this:

```twig
{% set days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] %}
{% set storeHours = entry.storeHours[1:]|merge(entry.storeHours[0:1]) %}

{% for dayHours in entry.storeHours %}
    <li>
        {{- days[loop.index0] }}: {{ dayHours.open|date('h:i a') }} - {{ dayHours.close|date('h:i a') -}}
    </li>
{% endfor %}
```
