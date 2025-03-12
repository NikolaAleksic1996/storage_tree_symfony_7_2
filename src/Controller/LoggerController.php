<?php

namespace App\Controller;

use App\Service\CustomLogger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LoggerController extends AbstractController
{
    public function __construct(private readonly CustomLogger $customLogger)
    {
    }

    #[Route('/logger', name: 'logger')]
    public function index(): Response
    {
        $this->customLogger->log("Hello from logger controller");
        return new Response('Logged successfully');
    }

    #[Route('/manual_logger', name: 'manual_logger')]
    public function manualLogger(CustomLogger $logger): Response
    {
        $customLogger = new CustomLogger($logger);// you need to explicit implement LoggerInterface to use this logger
        $customLogger->log('Hello from manual logger controller');
        return new Response('Logged manual successfully');
    }
}