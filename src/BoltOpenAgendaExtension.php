<?php

namespace Bolt\Extension\Leskis\BoltOpenAgenda;

use Bolt\Extension\Leskis\BoltOpenAgenda\OAEventsProvider;
use Bolt\Extension\SimpleExtension;
use CalendR\Extension\Silex\Provider\CalendRServiceProvider;
use Silex\Application;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * BoltOpenAgenda extension class.
 */
class BoltOpenAgendaExtension extends SimpleExtension
{

    /**
     * The callback function when {{ oa_nextEvents(agendaAlias) }} is used in a
     * template. Use it to display 10 next events.
     *
     * @param  string $agendaAlias OpenAgenda Alias
     * @return array               Collection of OpenAgenda Event
     */
    public function nextEventsFunction($agendaAlias)
    {
        $app      = $this->getContainer();
        $agendaId = $this->getAgendaIdFromAlias($agendaAlias);
        $events   = $app['calendr.event.providers']['oaevents']->getNextEvents(['agenda_id' => $agendaId]);

        return $events;
    }


    /**
     * The callback function when {{ oa_event(agendaAlias, eventId) }} is used
     * in a template. Use it to display a particular event.
     *
     * @param  string $agendaAlias OpenAgenda Alias
     * @param  string $eventId     OpenAgenda event id
     * @return \OAEvent            OpenAgenda Event
     */
    public function eventFunction($agendaAlias, $eventId)
    {
        $app      = $this->getContainer();
        $agendaId = $this->getAgendaIdFromAlias($agendaAlias);
        $event    = $app['calendr.event.providers']['oaevents']->getEvent([
            'agenda_id' => $agendaId,
            'event_id'  => $eventId
        ]);

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    protected function registerServices(Application $app)
    {
        $app['bolt-openagenda.config'] = $app->share(
           function ($app) {
               return new ParameterBag($this->getConfig() );
           }
        );

        $app->register(new CalendRServiceProvider(), array(
            'calendr.event.providers' => array(
                'oaevents' => new OAEventsProvider($app['bolt-openagenda.config'])
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigPaths()
    {
        return ['templates'];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigFunctions()
    {
        return [
            'oa_nextEvents' => 'nextEventsFunction',
            'oa_event'      => 'eventFunction'
        ];
    }

    /**
     * Get Agenda Id from an alias
     * @param  string $agendaAlias OpenAgenda Alias
     * @return string $agendaId    Id linked to the alias
     */
    protected function getAgendaIdFromAlias($agendaAlias)
    {
        $config = $this->getConfig();
        $agendaId = null;

        if (! array_key_exists('agendas', $config) || count($config['agendas']) == 0) {
            throw new OAException("You should specify extension configuration to connect your OpenAgenda");
        }

        foreach ($config['agendas'] as $agenda) {
            if ( array_key_exists('alias', $agenda) && $agenda['alias'] == $agendaAlias ) {
                if (! array_key_exists('id', $agenda) ) {
                    throw new OAException("We find alias " . $agendaAlias . " in your configuration, but your forget to specify the id, please add it.");
                }
                else {
                    $agendaId = $agenda['id'];
                    break;
                }
            }
        }
        if (is_null($agendaId) ) {
            throw new OAException("Alias " . $agendaAlias . " is not specify in your configuration and we can not connect your OpenAgenda");
        }

        return $agendaId;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'agendas' => []
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return 'Bolt OpenAgenda';
    }
}
