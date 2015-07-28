# Store Hours plugin for Craft

This plugin adds a new “Store Hours” field type to Craft, for collecting the opening and closing hours of a business for each day of the week.

## Plugin Installation

To install Store Hours, follow these steps:

1.  Upload the storehours/ folder to your craft/plugins/ folder.
2.  Go to Settings > Plugins from your Craft control panel and enable the Store Hours plugin.

## Template Rendering
    {% set days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] %}

    {% for day_hours in entry.storeHours %}
      <div>
        {{ days[loop.index0] }}: {{ day_hours.open|date('h:i a') }} - {{ day_hours.close|date('h:i a') }}
      </div>
    {% endfor %}

    #### Output
    
    Sunday: 11:00 AM - 05:00 PM
	Monday: 10:00 AM - 08:00 PM
	Tuesday: 10:00 AM - 08:00 PM
	Wednesday: 10:00 AM - 08:00 PM
	Thursday: 10:00 AM - 08:00 PM
	Friday: 10:00 AM - 08:00 PM
	Saturday: 10:00 AM - 07:00 PM
    
## OR (slight hack for Mon-Fri)


		{% set sunday = entry.storeHours[0] %}
        {% set mon_fri = entry.storeHours[1] %}
        {% set saturday = entry.storeHours[6] %}

        <div>
          <p>Mon-Fri: {{ mon_fri.open|date('ga') }}-{{ mon_fri.close|date('ga') }}</p>
          <p>Sat: {{ saturday.open|date('ga') }}-{{ saturday.close|date('ga') }}</p>
          <p>Sun: {{ sunday.open|date('ga') }}-{{ sunday.close|date('ga') }}</p>
        </div>
        
        #### Output
        Mon-Fri: 10am-8pm
		Sat: 10am-7pm
		Sun: 11am-5pm
        