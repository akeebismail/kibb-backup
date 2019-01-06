<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 1/6/19
 * Time: 3:55 PM
 */

namespace Kibb\Backup\Tasks\Cleanup;

use Carbon\Carbon;
class Period
{
    /** @var \Carbon\Carbon */
    protected $startDate;

    /** @var \Carbon\Carbon */
    protected $endDate;

    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function startDate(): Carbon
    {
        return $this->startDate->copy();
    }

    public function enDate(): Carbon
    {
        return $this->endDate->copy();
    }
}