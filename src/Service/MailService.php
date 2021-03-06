<?php

namespace App\Service;

use App\Entity\Flat;
use Swift_Mailer;
use Swift_Message;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Provides functionality to rendering email template and send mail to contact.
 */
class MailService
{
    /** @var Swift_Mailer */
    private $mailer;

    /** @var string $token */
    private $token;

    /** @var string $appBaseUrl */
    private $appBaseUrl;

    /** @var Environment $templating */
    private $templating;

    /**
     * MailService constructor.
     *
     * @param Swift_Mailer $mailer
     * @param Environment  $templating
     * @param string       $appBaseUrl
     * @param string       $token
     */
    public function __construct(Swift_Mailer $mailer, Environment $templating, string $appBaseUrl, string $token)
    {
        $this->mailer = $mailer;
        $this->token = $token;
        $this->appBaseUrl = $appBaseUrl;
        $this->templating = $templating;
    }

    /**
     * Renders email template and send mail to contact.
     *
     * @param Flat $flat
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     *
     * @return int
     */
    public function sendContactEmail(Flat $flat)
    {
        $message = (new Swift_Message('Information about created Flat.'))
            ->setFrom('noreply@local.com')
            ->setTo($flat->getEmail())
            ->setBody(
                $this->templating->render(
                    'email/new-flat.html.twig',
                    [
                        'flat' => $flat,
                        'token' => $this->token,
                        'baseUrl' => $this->appBaseUrl,
                    ]
                ),
                'text/html'
            )
        ;

        return $this->mailer->send($message);
    }
}
