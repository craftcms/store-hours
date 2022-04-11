# Store Hours for Craft CMS

This plugin adds a new “Store Hours” field type to Craft, for collecting the opening and closing hours (or any other time slots) of an organization for each day of the week.

## Requirements

This plugin requires Craft CMS 3.0.0-beta.20 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Store Hours”. Then press **Install** in its modal window.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require craftcms/store-hours

# tell Craft to install the plugin
./craft install/plugin store-hours
```

## Customizing Time Slots

Store Hours fields have a setting called “Time Slots” that lets you customize the time columns that will be visible in the field inputs. By default there will be two time slots, “Opening Time” and “Closing Time”, but you can customize those however you like.

![A Time Slots field setting with “Open”, “Close”, “Second Open”, and “Second Close” slots defined](assets/time-slots.png)

Each row you add here will end up adding a new column to the field inputs.

![A Store Hours field with four custom time slots](assets/field.png)

## Templating

You can loop through your Store Hours fields to access info for each day of the week (starting with Sunday):

```twig
<h3>Opening Hours</h3>
<dl>
  {% for day in entry.<FieldHandle> %}
    <dt>{{ day.name }}</dt>
    <dd>
      {% if day.isBlank %}
        Closed
      {% else %}
        {{ day.open|time }} - {{ day.close|time }}
      {% endif %}
    </dd>
  {% endfor %}
</dl>
```

The exact time format that will be used when outputting times with the `|time` filter depends on the current application locale. See the [Craft documentation](https://docs.craftcms.com/v3/dev/filters.html#time) for details on how it can be customized.

### Showing a Single Day’s Hours

You can get the hours for a single day using the following methods:

| Method | Day |
| `getSun()` | Sunday |
| `getMon()` | Monday |
| `getTue()` | Tuesday |
| `getWed()` | Wednesday |
| `getThu()` | Thursday |
| `getFri()` | Friday |
| `getSat()` | Saturday |
| `getYesterday()` | Yesterday |
| `getToday()` | Today |
| `getTomorrow()` | Tomorrow |

```twig
<h3>Today’s Hours</h3>
{% set today = entry.<FieldHandle>.today %}
{% if today.isBlank %}
  <p>Sorry, we’re closed today.</p>
  {% set tomorrow = entry.<FieldHandle>.tomorrow %}
  {% if not tomorrow.isBlank %}
    <p>We’ll be back open tomorrow at {{ tomorrow.open|time }}.</p>
  {% endif %}
{% else %}
  <p>We’re open from {{ today.open|time }} to {{ today.close|time }} today.</p>
{% endif %}
``` 

### Showing a Custom Day Range

To only show certain days of the week (e.g only Monday through Friday), use the `getRange()` field method. Pass two integers in to represent the start and end days of the range, using this mapping:

| Number | Day       |
| ------ | --------- |
| `0`    | Sunday    |
| `1`    | Monday    |
| `2`    | Tuesday   |
| `3`    | Wednesday |
| `4`    | Thursday  |
| `5`    | Friday    |
| `6`    | Saturday  |

```twig
{# Only show Monday-Friday #}
{% set range = entry.<FieldHandle>.getRange(1, 5) %}

{% for day in range %}
    {# ... #}
{% endfor %}
```

### Changing the Week Start Day

You can use the `getRange()` field method to return the full list of days with a different week start day. For example, if you want Monday to be the first day of the week, do this:

```twig
{# Show all days with Monday first #}
{% set range = entry.<FieldHandle>.getRange(1, 0) %}

{% for day in range %}
  {# ... #}
{% endfor %}
```

As a shortcut, you can also skip passing the end day when you want to do this:

```twig
{# Show all days with Monday first #}
{% set range = entry.<FieldHandle>.getRange(1) %}

{% for day in range %}
  {# ... #}
{% endfor %}
```

### Determining Whether All Slots are Blank

You can quickly find out whether all the time slots in a day were left blank using the `getIsBlank()` (`isBlank`) field method:

```twig
<h3>Opening Hours</h3>
<dl>
  {% for day in entry.<FieldHandle> %}
    <dt>{{ day.name }}</dt>
    <dd>
      {% if day.isBlank %}
        Closed
      {% else %}
        {{ day.open|time }} - {{ day.close|time }}
      {% endif %}
    </dd>
  {% endfor %}
</dl>
```
