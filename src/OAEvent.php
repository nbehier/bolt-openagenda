<?php

namespace Bolt\Extension\Leskis\BoltOpenAgenda;

use CalendR\Event\AbstractEvent;

/*
0 => array:42 [▼
    "uid" => 00000000
    "slug" => "ag-constitutive"
    "canonicalUrl" => "https://openagenda.com/..."
    "title" => array:1 [▼
      "fr" => "XXXX"
    ]
    "description" => array:1 [▼
      "fr" => "XXXX"
    ]
    "longDescription" => array:1 [▼
      "fr" => """
        XXXX\n
        \n
        XXXX
        """
    ]
    "keywords" => array:1 [▼
      "fr" => array:2 [▼
        0 => "XX"
        1 => "XXX"
      ]
    ]
    "html" => array:1 [▼
      "fr" => """
        <p>XXX</p>\n
        <p>XXX</p>\n
        """
    ]
    "image" => false
    "thumbnail" => false
    "originalImage" => false
    "age" => null
    "accessibility" => []
    "updatedAt" => "2017-02-05T14:34:17.000Z"
    "range" => array:2 [▼
      "fr" => "14 janvier, 09h00"
      "en" => "14 january, 09:00"
    ]
    "conditions" => null
    "registrationUrl" => null
    "locationName" => "XX - XXXX"
    "locationUid" => 000000
    "address" => "XXX"
    "postalCode" => "00000"
    "city" => "XXX"
    "district" => null
    "department" => "XXXX"
    "region" => "XXXX"
    "latitude" => 47,000000
    "longitude" => 0,000000
    "timings" => array:1 [▼
      0 => array:2 [▼
        "start" => "2017-01-14T08:00:00.000Z"
        "end" => "2017-01-14T13:00:00.000Z"
      ]
    ]
    "location" => array:22 [▼
      "uid" => 000000
      "name" => "XX - XXX"
      "slug" => "XX-XXX"
      "address" => "XXX"
      "image" => null
      "imageCredits" => null
      "postalCode" => "00000"
      "city" => "XXX"
      "district" => null
      "department" => "XXX"
      "region" => "XXX"
      "latitude" => 47,000000
      "longitude" => 0,000000
      "description" => []
      "access" => []
      "countryCode" => "fr"
      "website" => null
      "links" => []
      "phone" => null
      "tags" => null
      "timezone" => "Europe/Paris"
      "updatedAt" => "2017-02-05T12:03:20.000Z"
    ]
    "registration" => []
    "firstDate" => "2017-01-14"
    "firstTimeStart" => "09:00"
    "firstTimeEnd" => "14:00"
    "lastDate" => "2017-01-14"
    "lastTimeStart" => "09:00"
    "lastTimeEnd" => "14:00"
    "featured" => 0
    "custom" => []
    "contributor" => []
    "category" => array:2 [▼
      "label" => "XX X'XX"
      "slug" => "XX-XXX"
    ]
    "tags" => []
    "tagGroups" => []
  ]
*/

class OAEvent extends AbstractEvent
{
    /** @var array */
    protected $oa_json;

    /** @var string */
    protected $uid;

    /** @var \DateTime */
    protected $begin;

    /** @var \DateTime */
    protected $end;

    /**
     * Constructor
     * @param json $json_event OpenAgenda JSON Event
     */
    public function __construct($json_event)
    {
        if ( ! is_array($json_event) ) {
            throw new OAException('Event is not a json array');
        }
        if ( ! array_key_exists('uid', $json_event) || trim($json_event['uid']) == '' ) {
            throw new OAException('Event must have a UID');
        }
        if (   ! array_key_exists('timings', $json_event)
            || ! is_array($json_event['timings'])
            || count($json_event['timings']) == 0) {
            throw new OAException('Event must have timings');
        }
        if ( ! array_key_exists('start', $json_event['timings'][0])
             || trim($json_event['timings'][0]['start']) == 0 ) {
            throw new OAException('Event must have a begin timings');
        }
        if ( ! array_key_exists('end', $json_event['timings'][0])
             || trim($json_event['timings'][0]['end']) == 0 ) {
            throw new OAException('Event must have a end timings');
        }

        $this->oa_json = $json_event;
        $this->uid     = $json_event['uid'];
        $this->begin   = new \DateTime($json_event['timings'][0]['start']);
        $this->end     = new \DateTime($json_event['timings'][0]['end']);
    }

    /**
     * Get JSON OpenAgenda Event
     * @return json Event
     */
    public function getJson()
    {
        return $this->oa_json;
    }

    /**
     * Returns an unique identifier for the Event.
     * Could be any string, but MUST to be unique.
     *   ex : 'event-8', 'meeting-43'.
     *
     * @return string an unique event identifier
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Returns the event begin.
     *
     * @return \DateTime event begin
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Returns the event end.
     *
     * @return \DateTime event end
     */
    public function getEnd()
    {
        return $this->end;
    }
}
