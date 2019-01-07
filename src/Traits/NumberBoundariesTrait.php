<?php
namespace App\Traits;

trait NumberBoundariesTrait
{
    public function convertAndCheckNumbers(&$numberStart, &$numberEnd)
    {
        if ($numberStart !== null && is_numeric($numberStart))
        {
            throw new \UnexpectedValueException('Start number must be numeric.');
        }

        if ($numberEnd !== null && is_numeric($numberEnd))
        {
            throw new \UnexpectedValueException('End number must be numeric.');
        }

        if ($numberStart !== null && $numberEnd !== null && $numberEnd < $numberStart)
        {
            throw new \LogicException('Start number cannot be greater than end number.');
        }

        $numberStart = (int) $numberStart;
        $numberEnd   = (int) $numberEnd;
    }
}
