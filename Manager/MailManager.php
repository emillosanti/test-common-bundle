<?php

namespace SAM\CommonBundle\Manager;

use SAM\AddressBookBundle\Entity\ContactMergedReminder;
use SAM\AddressBookBundle\Entity\ContactMergedReminderAssignee;
use SAM\AddressBookBundle\Entity\UserReadContactMerged;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class MailManager
 * @package SAM\CommonBundle\Manager
 */
class MailManager
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var EngineInterface
     */
    private $templateEngine;

    /**
     * @var string
     */
    private $sender;

    /**
     * MailManager constructor.
     *
     * @param \Swift_Mailer $mailer
     * @param EngineInterface $templateEngine
     */
    public function __construct(\Swift_Mailer $mailer, EngineInterface $templateEngine)
    {
        $this->mailer = $mailer;
        $this->templateEngine = $templateEngine;
    }

    /**
     * @required
     *
     * @param string $sender
     *
     * @return $this
     */
    public function setSender($sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @param UserReadContactMerged $userRead
     */
    public function sendUserReadContactMergedRequest(UserReadContactMerged $userRead)
    {
        $from = $this->sender;
        $to = $userRead->getTargetUser()->getEmail();
        $subject = sprintf('Demande d\'accès pour le contact de %s', $userRead->getContactMerged()->getFullName());
        $body = $this->templateEngine->render('@SAMAddressBook/Email/user_read_request.html.twig', [
            'userRead' => $userRead,
            'subject' => $subject
        ]);
        $this->sendMessage($from, $to, $subject, $body);
    }

    /**
     * @param UserReadContactMerged $userRead
     */
    public function sendUserReadContactMergedValidated(UserReadContactMerged $userRead)
    {
        $from = $this->sender;
        $to = $userRead->getUser()->getEmail();
        $subject = 'Demande de contact acceptée';
        $body = $this->templateEngine->render('@SAMAddressBook/Email/user_read_validated.html.twig', [
            'userRead' => $userRead,
            'subject' => $subject
        ]);
        $this->sendMessage($from, $to, $subject, $body);
    }

    /**
     * @param ContactMergedReminderAssignee $assignee
     * @param ContactMergedReminder $reminder
     */
    public function sendContactMergedReminderAssigneeDue(ContactMergedReminder $reminder, ContactMergedReminderAssignee $assignee)
    {
        $to = $assignee->getUser()->getEmail();
        $subject = 'Rappel pour ' . $reminder->getContactMerged()->getFullName() . ' - ' . $reminder->getMeansOfContactAsString();
        $body = $this->templateEngine->render('@SAMAddressBook/Email/contact_merged_reminder_assignee_due.html.twig', [
            'reminder' => $reminder,
            'assignee' => $assignee,
            'subject' => $subject
        ]);
        $this->sendMessage($this->sender, $to, $subject, $body);
    }

    /**
     * @param string $email
     * @param string $path
     */
    public function sendContactMergedVCard($contact, $user, $email, string $path)
    {
        $subject = 'Partage du contact ' . $contact->getFullName() . ' par ' . $user->getFullName();
        $body = $this->templateEngine->render('@SAMAddressBook/Email/contact_share_vcard.html.twig', [
            'contact' => $contact,
            'user' => $user,
            'subject' => $subject
        ]);
        $this->sendMessage($this->sender, $email, $subject, $body, $path);
    }

    /**
     * @param $from
     * @param $to
     * @param $subject
     * @param $body
     * @param string|null $attachment
     * @param string $contentType
     * @param bool $cc
     */
    public function sendMessage($from, $to, $subject, $body, string $attachment = null, $contentType = 'text/html', $cc = null)
    {
        $message = \Swift_Message::newInstance();
        $message->setFrom($from);
        $message->setTo($to);
        
        if ($cc && is_array($cc) && count($cc)) {
            $message->setCC($cc);
        }

        $message
            ->setSubject($subject)
            ->setBody($body)
            ->setContentType($contentType);

        if ($attachment) {
            $message->attach(\Swift_Attachment::fromPath($attachment));
        }

        $this->mailer->send($message);
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return EngineInterface
     */
    public function getTemplateEngine()
    {
        return $this->templateEngine;
    }
}
