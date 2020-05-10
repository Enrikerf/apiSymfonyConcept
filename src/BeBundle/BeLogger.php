<?php
/**
 * User: enrikerf
 * Date: 09/05/2017
 * Time: 12:09
 */

namespace App\BeBundle;

use App\Data\Entity\User;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Security;
use function count;


class BeLogger {

    public const PROD = 'prod';
    public const DEV = 'dev';
    private Logger   $logger;
    private string   $environment;
    private ?string  $level        = null;
    private ?string  $invokerClass = null;
    /** @var User $user */
    private          $user;
    private Security $security;

    public function __construct(Security $security, ParameterBagInterface $parameterBag) {
        $this->environment = $parameterBag->get('env');
        $this->logger = new Logger('logBE');
        $this->security = $security;
    }

    public function setContext($invokerClass, $level = null, $user = null): void {
        if (null !== $level) {
            $this->level = $level;
        } else {
            null !== $this->level ?: $this->level = Logger::DEBUG;
        }
        if (null !== $user) {
            $this->user = $user;
        } else {
            null !== $this->user ?: $this->user = 'Undefined ';
        }
        if (null !== $invokerClass) {
            $this->invokerClass = $invokerClass;
        } else {
            null !== $this->invokerClass ?: $this->invokerClass = ' - ';
        }
        $this->resetFile();
    }

    public function info($message, $data = null): void {
        $this->setContext($this->invokerClass, Logger::INFO, $this->user);
        null !== $data ?: $data = [];
        $this->logger->info($this->getUserName().' from '.$this->invokerClass.' -> '.$message, $data);
    }

    public function debug($message, $data = null): void {
        $this->setContext($this->invokerClass, Logger::DEBUG, $this->user);
        null !== $data ?: $data = [];
        $this->logger->debug($this->getUserName().' from '.$this->invokerClass.' -> '.$message, $data);
    }

    public function error($message, $data = null): void {
        $this->setContext($this->invokerClass, Logger::ERROR, $this->user);
        null !== $data ?: $data = [];
        $this->logger->error($this->getUserName().' from '.$this->invokerClass.' -> '.$message, $data);
    }

    public function critical($message, $data = null): void {
        $this->setContext($this->invokerClass, Logger::CRITICAL, $this->user);
        null !== $data ?: $data = [];
        $this->logger->critical($this->getUserName().' from '.$this->invokerClass.' -> '.$message, $data);
    }

    public function alert($message, $data = null): void {
        $this->setContext($this->invokerClass, Logger::ALERT, $this->user);
        null !== $data ?: $data = [];
        $this->logger->alert($this->getUserName().' from '.$this->invokerClass.' -> '.$message, $data);
    }

    public function warning($message, $data = null): void {
        $this->setContext($this->invokerClass, Logger::WARNING, $this->user);
        null !== $data ?: $data = [];
        $this->logger->warning($this->getUserName().' from '.$this->invokerClass.' -> '.$message, $data);
    }

    public function setInvokerClass($invokerClass): void {
        if (null !== $invokerClass) {
            $this->invokerClass = $invokerClass;
        } else {
            $this->invokerClass ?: $this->invokerClass = ' - ';
        }
    }

    private function getUserName(): string {
        if (!$this->security->getUser()) {
            return 'Undefined';
        }

        return $this->security->getUser()->getUsername();
    }

    private function resetFile(): void {
        try {
            (count($this->logger->getHandlers()) === 0) ?: $this->logger->popHandler();
            $this->logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/'.$this->environment.'/'.$this->getLevelFileName($this->level).'.log',
                $this->level));
        } catch (Exception $e) {
        }
    }

    private function getLevelFileName($level): string {
        switch ($level) {
            case Logger::CRITICAL:
                $fileName = 'critical';
                break;
            case Logger::DEBUG:
                $fileName = 'debug';
                break;
            case Logger::ERROR:
                $fileName = 'error';
                break;
            case Logger::INFO:
                $fileName = 'info';
                break;
            case Logger::WARNING:
                $fileName = 'warning';
                break;
            case Logger::ALERT:
                $fileName = 'alert';
                break;
            default:
                $fileName = 'error';
        }

        return $fileName;
    }
}