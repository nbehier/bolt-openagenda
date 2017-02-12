<?php

namespace Bolt\Extension\Leskis\BoltOpenAgenda;

use CalendR\Event\Provider\ProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\ParameterBag;

class OAEventsProvider implements ProviderInterface
{
    /** @var ParameterBag */
    protected $config;

    /**
     * Constructor
     * @param ParameterBag $config Extension configuration
     */
    public function __construct(ParameterBag $config)
    {
        $this->config = $config;
    }

    /**
     * Fetch events for a period
     *
     * @param  \DateTime $begin   Period begin datetime
     * @param  \DateTime $end     Period end datetime
     * @param  array     $options Options, like agenda_id
     * @return array              Collection of OpenAgenda Event
     */
    public function getEvents(\DateTime $begin, \DateTime $end, array $options = array())
    {
        $events = array();

        if ( ! array_key_exists('agenda_id', $options) || trim($options['agenda_id']) == '' ) {
            throw new OAException("Agenda id missing");
        }

        $events = $this->requestOA(
            $options['agenda_id'],
            [
                'passed' => true,
                'from'   => $begin,
                'to'     => $end
            ]
        );

        return $events;
    }

    /**
     * Fetch next 10 events
     *
     * @param  array  $options Options, like agenda_id
     * @return array           Collection of OpenAgenda Event
     */
    public function getNextEvents(array $options = array())
    {
        $events = array();

        if ( ! array_key_exists('agenda_id', $options) || trim($options['agenda_id']) == '' ) {
            throw new OAException("Agenda id missing");
        }

        $events = $this->requestOA($options['agenda_id'], ['limit' => 10]);

        return $events;
    }

    /**
     * Fetch a particular event
     *
     * @param  array    $options Options, like agenda_id
     * @return \OAEvent          OpenAgenda Event
     */
    public function getEvent(array $options = array())
    {
        $event = null;

        if ( ! array_key_exists('agenda_id', $options) || trim($options['agenda_id']) == '' ) {
            throw new OAException("Agenda id missing");
        }
        if ( ! array_key_exists('event_id', $options) || trim($options['event_id']) == '' ) {
            throw new OAException("Event id missing");
        }

        $event = $this->requestOA($options['agenda_id'], ['uids' => $options['event_id']]);

        if (count($event) > 0) { $event = $event[0]; }

        return $event;
    }


    /**
     * Fetch OpenAgenda JSON API
     *
     * @param  string $agendaId OpenAgenda Id
     * @param  array  $options  Request clause like limit, passed, from, to, uids…
     * @return array            Collection of OpenAgenda Event
     *
     * @see https://openagenda.zendesk.com/hc/fr/articles/203034982-L-export-JSON-d-un-agenda
     * @see https://openagenda.zendesk.com/hc/fr/articles/210127965-Les-diff%C3%A9rentes-URLs-de-votre-agenda-int%C3%A9gr%C3%A9
     */
    protected function requestOA($agendaId, array $options = array())
    {
        if ( trim($agendaId) == '' ) { throw new OAException('Agenda id missing'); }

        $url      = 'https://openagenda.com/agendas/' . urlencode($agendaId) . '/events.json';
        $client   = new Client();
        $events   = [];
        $response = null;
        $query    = ['oaq' => []];

        if (array_key_exists('limit', $options) && is_numeric($options['limit']) && $options['limit'] > 0) {
            $query['limit'] = $options['limit'];
        }

        if (array_key_exists('passed', $options) && $options['passed'] == true) {
            $query['oaq']['passed'] = '1';
        }

        if (array_key_exists('from', $options) && is_a($options['from'], 'DateTime')) {
            $query['oaq']['from'] = $options['from']->format('Y-m-d');
        }

        if (array_key_exists('to', $options) && is_a($options['to'], 'DateTime')) {
            $query['oaq']['to'] = $options['to']->format('Y-m-d');
        }

        if (array_key_exists('uids', $options) ) {
            $query['oaq']['uids'][] = $options['uids'];
        }

        try {
            $res = $client->request('GET', $url, [
                'query' => $query
            ]);

            if ($res->getStatusCode() != 200) {
                throw new OAException("Error Processing Request to OpenAgenda API", $res->getStatusCode() );
            }

            $response = json_decode($res->getBody(), true);

            if (array_key_exists('success', $response) && $response['success'] == false ) {
                throw new OAException("Error Processing Request to OpenAgenda API", $response['message']);
            }
        }
        catch (RequestException $e) {}

        if ( array_key_exists('events', $response) && count($response['events']) > 0) {
            foreach ($response['events'] as $event) {
                array_push($events, new OAEvent($event));
            }
        }

        return $events;
    }
}
