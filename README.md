OpenAgenda for Bolt
===================

This [bolt.cm](https://bolt.cm/) extension get easier to display
[OpenAgenda](https://openagenda.com/?lang=en) events on your website (OpenAgenda: Get an efficient public calendar now).

Extension uses also [CalendR](http://yohan.giarel.li/CalendR/index.html) PHP lib,
to manage calendar view and load json OpenAgenda events on it.

### Requirements
- Bolt 3.x installation
- One or several agendas on [OpenAgenda](https://openagenda.com/?lang=en) - [Help](https://openagenda.zendesk.com/hc/fr)

### Installation
1. Login to your Bolt installation
2. Go to "View/Install Extensions" (Hover over "Extras" menu item)
3. Type `OpenAgenda` into the input field
4. Click on the extension name
5. Click on "Browse Versions"
6. Click on "Install This Version" on the latest stable version

### Extension Configuration
```(yml)
agendas:
    - alias: test1   # Any alias you want, to identify your OpenAgenda instance on Bolt
      id:    123456  # Your OpenAgenda Id
```

### CalendR Helper
[CalendR](http://yohan.giarel.li/CalendR/index.html) lib enables to manage a calendar and the containing events.
It comes with a [Silex provider](http://yohan.giarel.li/CalendR/silex.html) and [Twig Extension](http://yohan.giarel.li/CalendR/twig.html).
To get events from your OpenAgenda, specify your `agenda_id` on calendr_events` call.
```(twig)
{% set year   = calendr_year(requestYear) %}
{% set events = calendr_events(year, {'agenda_id': 16163963}) %}
```

My agenda page looks like:
```(twig)
{% set currentYear  = now|date('Y') %}
{% set requestYear  = (app.request.get('agendayear')|default(currentYear)) %}
{% set aWeek  = calendr_week(2017, 01) %}
{% set year   = calendr_year(requestYear) %}
{% set events = calendr_events(year, {'agenda_id': 16163963}) %}
<div class="calendar">

    {% for month in year %}
    <div class="row">
        <h3 class="calendar__month"><span>{{ month|localedatetime('%B') }} {{ month.format('Y') }}</span></h3>
        <div class="columns large-4">
            <table class="calendar">
                <thead>
                    <tr>
                        {% for aDay in aWeek %}
                        <th>{{ aDay|localedatetime('%a')|first }}</th>
                        {% endfor %}
                    </tr>
                </thead>
                <tbody>
                {% for week in month %}
                    <tr>
                        {% for day in week %}
                        <td class="
                            {% if not month.includes(day) %}calendar__day--outofmonth{% endif %}
                            {% if day.isCurrent %}calendar__day--current{% endif %}
                            {% if events.find(day)|length > 0 %}calendar__day--withevents{% endif %}">
                            {{ day.getBegin|localedatetime('%d') }}
                        </td>
                        {% endfor %}
                    </tr>
                {% endfor %}
                </tbody>
            </table>
        </div>
        <div class="columns large-8">
            <ul class="list--alt">
            {% for event in events.find(month) %}
                <li class="list__item--event">
                    <h4><a href="{{ path('agendaevent', {'agendaalias': 'test1', 'eventuid': event.getJSON.uid, 'eventslug': event.getJSON.slug }) }}">{{ event.getJSON.title.fr }}</a></h4> {% if event.getJSON.category|length > 0 %}<span class="label radius category">{{ event.getJSON.category.label }}</span>{% endif %}
                    <div>
                        <span class="date">{{ event.begin|localedatetime('%A %d/%m at %H:%M') }}</span>{% if event.getJSON.description.fr is not empty %}<span class="description"> - {{ event.getJSON.description.fr }}</span>{% endif %}
                    </div>
                </li>
            {% else %}
                <li><p>No future events currently scheduled.</p></li>
            {% endfor %}
            </ul>
        </div>
    </div>
    {% endfor %}
</div>
```

### Twig Helper
#### oa_nextEvents function
If you want to display the next events of `test1` agenda, use `oa_nextEvents`
twig function. It returns an array of events.
All OpenAgenda data could be retrieve with `getJSON` method.
```(twig)
<ul>
    {% for event in oa_nextEvents('test1')|slice(0, 5) %}
    <li><span class="date">{{ event.begin|localedatetime('%d/%m') }}</span> <a href="{{ path('agendaevent', {'agendaalias': 'zdt', 'eventuid': event.getJSON.uid, 'eventslug': event.getJSON.slug }) }}">{{ event.getJSON.title.fr }}</a></li>
    {% else %}
    <li>No future events currently scheduled.</li>
    {% endfor %}
</ul>
```

#### oa_nextEvents function
If you want to display a particular event of `test1`agenda, use `oa_event`
twig function. It returns an event.

```(twig)
{% set oaevent = oa_event(app.request.get('agendaalias'), app.request.get('eventuid')) %}
{% if oaevent is not empty %}
    {% set event = oaevent.getJSON %}

    <section>
        <header><p><a href="{{ path('agenda') }}" class="button tiny">Back to agenda</a></p></header>

    {% if event.image is not empty %}
        <img src="{{ event.image }}" alt="" />
    {% endif %}

        <h1>{{ event.title.fr }}</h1>

    {% if event.description.fr is not empty %}
        <p class="head">{{ event.description.fr }}</p>
    {% endif %}

    {% if event.category|length > 0 %}<p><span class="label radius">{{ event.category.label }}</span></p>{% endif %}

    {% if event.timings[0] is defined and event.timings[0].start is not empty %}
        <p><strong>Timing :</strong> {{ event.timings[0].start|localedatetime('%A %e %B %Y') }}
        {% set event_dtStart = event.timings[0].start|localedatetime('%y%m%d') %}
        {% if event.timings[0].end is not empty %}
            {% set event_dtEnd = event.timings[0].end|localedatetime('%y%m%d') %}
            {% if event_dtStart == event_dtEnd %}
                from {{ event.timings[0].start|localedatetime('%H:%M') }} to {{ event.timings[0].end|localedatetime('%H:%M') }}
            {% else %}
                at {{ event.timings[0].start|localedatetime('%H:%M') }} to {{ event.timings[0].start|localedatetime('%A %e %B %Y à %H:%M') }}
            {% endif %}
        {% else %}
            at {{ event.timings[0].start|localedatetime('%H:%m') }}
        {% endif %}
        </p>
    {% endif %}

    {% if event.html.fr is not empty %}
        <div>
            {{ event.html.fr|raw }}
        </div>
    {% endif %}

    {% if event.location is defined and event.location.name is not empty %}
        <p><strong>Location :</strong> {{ event.location.name }}{% if event.location.address is not empty %}, {{ event.location.address }}{% endif %}{% if event.location.city is not empty %}, {{ event.location.postalCode }} {{ event.location.city }}{% endif %}</p>
    {% endif %}

    {% if event.location is defined and event.location.latitude is not empty %}
        <div id="oa-event__map" style="height:200px; width:100%;"></div>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.2/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.0.2/dist/leaflet.js"></script>
        <script>
        var map = L.map('oa-event__map').setView([{{ event.location.latitude|replace({",": "."}) }}, {{ event.location.longitude|replace({",": "."}) }}], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        L.marker([{{ event.location.latitude|replace({",": "."}) }}, {{ event.location.longitude|replace({",": "."}) }}]).addTo(map)
        </script>
    {% endif %}
    </section>
{% else %}
<div class="warning callout">
  <p>This page did not match any events.</p>
</div>
{% endif %}
```

#### Extension Config
Your configuration could be access from twig template with:
`{% set extension_config = app['bolt-openagenda.config'] %}`

### Routing example
You could add some routes on Bolt to manage your agenda pages:
```(yml)
agendaevent:
    path: /agenda/evenet/{agendaalias}/{eventuid}/{eventslug}
    defaults:
        _controller: controller.frontend:record
        contenttypeslug: page
        slug: agenda-event
    requirements:
        agendaalias: '[a-z0-9-_]+'
        eventuid:  '[0-9]+'
        eventslug: '[a-z0-9-_]+'

agendayear:
    path: /agenda/{agendayear}
    defaults:
        _controller: controller.frontend:record
        contenttypeslug: page
        slug: agenda
    requirements:
        agendayear: '20[1-2][0-9]'

agenda:
    path: /agenda
    defaults:
        _controller: controller.frontend:record
        contenttypeslug: page
        slug: agenda
```

---

### Credits
Extension icon inspired by [work of Rockicon](https://thenounproject.com/term/monthly-calendar/523605/) on Noun Project

### License
This Bolt extension is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
