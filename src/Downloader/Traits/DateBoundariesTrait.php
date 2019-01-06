<?php
namespace App\Downloader\Traits;

trait DateBoundariesTrait
{
    public function convertAndCheckDates(&$dateStart, &$dateEnd)
    {
        $dateStart = ($dateStart === null)? null : new \DateTime($dateStart);
        $dateEnd   = ($dateEnd === null)?   null : new \DateTime($dateEnd);

        if ($dateStart !== null && $dateEnd !== null && $dateEnd < $dateStart)
        {
            throw new \LogicException('Start date cannot be greater than end date.');
        }
    }
}
