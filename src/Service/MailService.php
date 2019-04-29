<?php

/*
 * This file is part of the Symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\Flat;
use Swift_Mailer;
use Swift_Message;
use Twig\Environment;

class MailService
{
    /** @var Swift_Mailer */
    private $mailer;

    /** @var string $token */
    private $token;

    /** @var string $appBaseUrl */
    private $appBaseUrl;
    /**
     * @var Environment
     */
    private $templating;

    /**
     * MailService constructor.
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
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
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
