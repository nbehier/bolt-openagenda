<?php

/*
 * This file is part of CalendR, a Fréquence web project.
 *
 * (c) 2012 Fréquence web
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CalendR\Event;

use CalendR\Period\PeriodInterface;

/**
 * Abstract class that ease event manipulation.
 *
 * @author Yohan Giareli <yohan@giarel.li>
 */
abstract class AbstractEvent implements EventInterface
{
    /**
     * Check if the given date is during the event.
     *
     * @param \DateTime $datetime
     *
     * @return bool true if $datetime is during the event, false otherwise
     */
    public function contains(\DateTime $datetime)
    {
        return $this->getBegin() <= $datetime && $datetime < $this->getEnd();
    }

    /**
     * Check if the given period is during the event.
     *
     * @param PeriodInterface $period
     *
     * @return bool true if $period is during the event, false otherwise
     */
    public function containsPeriod(PeriodInterface $period)
    {
        return $this->contains($period->getBegin()) && $this->contains($period->getEnd());
    }

    /**
     * Check if the event is during the given period.
     *
     * @param PeriodInterface $period
     *
     * @return bool true if the event is during $period, false otherwise
     */
    public function isDuring(PeriodInterface $period)
    {
        return $this->getBegin() >= $period->getBegin() && $this->getEnd() < $period->getEnd();
    }
}
