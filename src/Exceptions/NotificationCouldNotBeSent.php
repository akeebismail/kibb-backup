<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 7:53 AM
 */

namespace Kibb\Backup\Exceptions;

use Exception;
class NotificationCouldNotBeSent extends Exception
{

    public static function noNotificationClassForEvent($event): self
    {
        $eventClass = get_class($event);
        return new static("There is no notification class that can handle event `{$eventClass}`.");
    }
}