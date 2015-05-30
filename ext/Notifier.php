<?php
namespace Codeception\Extension;


use Codeception\Event\PrintResultEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Platform\Extension;
use Joli\JoliNotif\NotifierFactory;

/**
 * Shows a desktop notification including success, error, fail and skipped count
 * @package Codeception\Extension
 */

class Notifier extends Extension
{

    // we are listening for events
    static $events = [
        Events::RESULT_PRINT_AFTER => 'after',
        Events::TEST_SUCCESS => 'success',
        Events::TEST_FAIL => 'fail',
        Events::TEST_ERROR => 'error',
        Events::TEST_SKIPPED => 'skipped',
    ];

    private $success_count = 0;
    private $fail_count = 0;
    private $error_count = 0;
    private $skipped_count = 0;


    public function __construct()
    {
        if (!class_exists('Joli\JoliNotif\Notification')) {
            throw new ExtensionException('Notifier', 'Notification extension requires jolicode/jolinotif');
        }
    }

    public function success(TestEvent $e)
    {
        $this->success_count++;
    }

    public function fail(TestEvent $e)
    {
        $this->fail_count++;
    }

    public function error(TestEvent $e)
    {
        $this->error_count++;
    }

    public function skipped(TestEvent $e)
    {
        $this->skipped_count++;
    }

    public function after(PrintResultEvent $e)
    {
        $notifier = NotifierFactory::create();
        if ($notifier) {

            $notifier->send((new \Joli\JoliNotif\Notification())
                ->setTitle("Codeception Test Result")
                ->setBody($this->formatBody())
                ->setIcon(basename(__DIR__) . '/' . $this->getIcon()));
        }

    }

    private function formatBody()
    {

        return sprintf('Success: %d Error: %d Fail: %d Skipped: %d',
            $this->success_count,
            $this->error_count,
            $this->fail_count,
            $this->skipped_count);
    }

    private function getIcon()
    {
        if ($this->error_count === 0 && $this->fail_count === 0 && $this->skipped_count === 0) {
            return 'success.png';
        } elseif ($this->error_count > 0) {
            return 'error.png';
        } elseif ($this->fail_count > 0) {
            return 'fail.png';
        } else {
            return 'info.png';
        }
    }
}