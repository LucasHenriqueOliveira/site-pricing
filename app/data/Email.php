<?php

namespace App\Data;

class Email extends Source {
    private $_mailbox;
    private $_imported;
    private $_doClearInbox;


    public function __construct($params = []) {
        if ($params['doClearInbox']) {
            $this->_doClearInbox = $params['doClearInbox'];
        }
        parent::__construct($params);
    }

    public function login() {
        $this->_mailbox = new \PhpImap\Mailbox('{imap.gmail.com:993/imap/ssl}INBOX', env('PD_MAILBOX_USERNAME'), env('PD_MAILBOX_PASSWORD'), __DIR__);
    }

    public function mailbox() {
        return $this->_mailbox;
    }

    public function getMailBySubject($subject) {
        $mails = $this->mailbox()->searchMailbox('SUBJECT "'.$subject.'"');

        if (!count($mails)) {
            return null;
        }

        // Get the earliest one
        $mailNumber = $mails[0];
        $mail = $this->mailbox()->getMail($mailNumber);
        if (!$mail) {
            return null;
        }
        return ['mail' => $mail, 'mailNumber' => $mailNumber];

    }

    public function moveEmailsOutOfInbox() {
        $imported = $this->_imported;
        foreach ($imported as $import) {
            $this->_mailbox->moveMail($import, "[Gmail]/All Mail");
        }
        $this->_imported = [];
    }


    public function imported() {
        return $this->_imported;
    }

    public function addToImported($mailNumber) {
        $this->_imported[] = $mailNumber;
    }

    public function clearImported() {
        $this->_imported = [];
    }

    public function doClearInbox() {
        return $this->_doClearInbox;
    }


}