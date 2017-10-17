<?php

namespace AppBundle\Service;

use Psr\Log\LoggerInterface;

use QuadernoBase;
use QuadernoContact;
use QuadernoDocumentItem;
use QuadernoInvoice;

use AppBundle\Entity\Invoice;
use AppBundle\Service\Protocols;

class Quaderno {

    private $logger, $protocols;

    public function __construct($api_key, $api_url, LoggerInterface $logger, Protocols $protocols) {
        QuadernoBase::init($api_key, $api_url);
        $this->logger = $logger;
        $this->protocols = $protocols;
        if (!QuadernoBase::ping()) {
            $this->logger->error('Quaderno does not respond to ping!');
        }
    }

    public function getInvoice($orderNumber) {
        $found = QuadernoInvoice::find(array('q' => $orderNumber));
        if (count($found) < 1) {
            return null;
        }
        $invoice = new Invoice();
        $invoice->setUser(null);
        $invoice->setEmittedAt(new \DateTime($found[0]->__get('issue_date')));
        $invoice->setNumber($found[0]->__get('number'));
        $invoice->setUrl($found[0]->__get('pdf'));
        $invoice->setQuadernoId($found[0]->__get('id'));
        return $invoice;
    }

    public function createInvoice($theUser, $protocol) {
        $qInvoice = new QuadernoInvoice(array(
            'payment_method' => 'wire_transfer',
            'currency' => 'EUR'
        ));

        if ($protocol->getInvoice() != null) {
            return null;
        }

        $contact = $this->findContact($theUser);
        if ($contact == null) {
            $contact = $this->createContact($theUser);
        }

        if ($contact == null) {
            return null;
        }

        $tax = array(
            'name' => 'iva',
            'rate' => 21
        );

        $item = new QuadernoDocumentItem(array(
            'description' => $this->protocols->getName($protocol->getIdentifier()),
            'unit_price' => $protocol->getPrice() / (100 + $tax['rate']),
            'quantity' => 1,
            'tax_1_name' => $tax['name'],
            'tax_1_rate' => $tax['rate']
        ));

        $qInvoice->addItem($item);
        $qInvoice->addContact($contact);

        if (!$qInvoice->save()) {
            $this->logErrors(
                'Error while creating invoice for protocol ' . print_r($protocol, true),
                $qInvoice->errors
            );
            return null;
        }

        $invoice = new Invoice();
        $invoice->setUser($theUser->getId());
        $invoice->setEmittedAt(new \DateTime($qInvoice->__get('issue_date')));
        $invoice->setNumber($qInvoice->__get('number'));
        $invoice->setUrl($qInvoice->__get('pdf'));
        $invoice->setQuadernoId($qInvoice[0]->__get('id'));
        return $invoice;
    }

    public function createContact($user) {
        $contact = new QuadernoContact(array(
            'first_name' => $user->getCompanyName(),
            'vat_number' => $user->getCif(),
            'email' => $user->getEmail(),
            'street_line_1' => $user->getAddress(),
            'contact_name' => $user->getContactPerson(),
            'notes' => "Número de empleados: " . $user->getNumberEmployees()
                        .", sector: " .  $user->getSector()
        ));
        if (!$contact->save()) {
            $this->logErrors(
                'Error while creating contact for ' . print_r($user, true),
                $contact->errors
            );
            return null;
        }
        $user->setQuadernoId($contact->id);
        return $contact;
    }

    public function findContact($user) {
        $id = $user->getQuadernoId();
        if ($id == null) {
            return null;
        }
        return QuadernoContact::find($id);
    }

    private function logErrors($message, $errors) {
        $this->logger->error($message);
        foreach ($errors as $field => $field_errors) {
            $this->logger->error("    " . $field . ":");
            foreach ($field_errors as $error) {
                $this->logger->error("     - " . $error);
            }
        }
    }

}
