<?php

namespace App\Controller;

use App\Service\CustomLogger;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoggerController extends AbstractController
{
    private LoggerInterface $logger;
    public function __construct(private readonly CustomLogger $customLogger, LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/logger', name: 'logger')]
    public function index(): Response
    {
        $this->logger->info('Hello from logger');
        $this->customLogger->log("Hello from custom logger");
        return new Response('Logged successfully');
    }

    #[Route('/manual_logger', name: 'manual_logger')]
    public function manualLogger(CustomLogger $logger): Response
    {
        $customLogger = new CustomLogger($logger);
        $customLogger->log('Hello from manual logger controller');
        return new Response('Logged manual successfully');
    }
}
