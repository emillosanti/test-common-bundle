<?php

use League\FactoryMuffin\Faker\Facade as Faker;

$this->define(\AppBundle\Entity\User::class)->setDefinitions([
    'username' => Faker::username(),
    'email' => Faker::unique()->email(),
    'password' => Faker::password(),
    'firstName' => Faker::firstName(),
    'lastName' => Faker::lastName(),
    'code' => Faker::randomNumber(3)
]);

$this->define(\AppBundle\Entity\Company::class)->setDefinitions([
    'name' => Faker::unique()->company(),
    'website' => Faker::domainName()
]);

$this->define(\SAM\AddressBookBundle\Entity\Contact::class)->setDefinitions([
    'firstName' => Faker::firstName(),
    'lastName' => Faker::lastName(),
    'user' => 'entity|' . \AppBundle\Entity\User::class,
    'company' => 'entity|' . \AppBundle\Entity\Company::class
]);

$this->define(\AppBundle\Entity\Category::class)->setDefinitions([
    'name' => Faker::unique()->word()
]);

$this->define(\SAM\AddressBookBundle\Entity\ContactMerged::class)->setDefinitions([
    'firstName' => Faker::firstName(),
    'lastName' => Faker::lastName(),
    'company' => 'entity|' . \AppBundle\Entity\Company::class,
    'contact' => 'entity|' . \SAM\AddressBookBundle\Entity\Contact::class,
])->setCallback(function (\SAM\AddressBookBundle\Entity\ContactMerged $contactMerged) {
    $contact = $this->create(\SAM\AddressBookBundle\Entity\Contact::class);
    $contactMerged->addCategory($this->create(\AppBundle\Entity\Category::class));
    $mail = $this->create(\AppBundle\Entity\Email::class);
    $mail->setContact($contact);
    $contactMerged->addEmail($mail);
    $phone = $this->create(\AppBundle\Entity\Phone::class);
    $phone->setContact($contact);
    $contactMerged->addPhone($phone);
});

$this->define(\AppBundle\Entity\Prospect::class)->setDefinitions([
    'status' => \SAM\ProspectBundle\Entity\Prospect::STATUS_TO_CONTACT,
    'manager' => 'entity|' . \AppBundle\Entity\User::class,
    'company' => 'entity|' . \AppBundle\Entity\Company::class,
    'contactPrimary' => 'entity|' . \SAM\AddressBookBundle\Entity\ContactMerged::class
]);

$this->define(\AppBundle\Entity\Tag::class)->setDefinitions([
    'name' => Faker::unique()->word()
]);

$this->define(\AppBundle\Entity\Address::class)->setDefinitions([
    'type' => \AppBundle\Entity\Address::TYPE_BUSINESS,
    'address' => Faker::streetAddress(),
    'city' => Faker::city(),
    'country' => Faker::country()
]);

$this->define(\AppBundle\Entity\Email::class)->setDefinitions([
    'type' => \AppBundle\Entity\Email::TYPE_EMAIL,
    'email' => Faker::unique()->email(),
    'contact' => 'entity|' . \SAM\AddressBookBundle\Entity\Contact::class,
]);

$this->define(\AppBundle\Entity\Phone::class)->setDefinitions([
    'type' => \AppBundle\Entity\Phone::TYPE_PROFESSIONAL,
    'prefix' => '+19.9',
    'number' => Faker::randomNumber(6),
    'contact' => 'entity|' . \SAM\AddressBookBundle\Entity\Contact::class,
]);

$this->define(\AppBundle\Entity\ContactMergedReminder::class)->setDefinitions([
    'means_of_contact' => \SAM\AddressBookBundle\Entity\ContactMergedReminder::MEANS_OF_CONTACT_PHONE,
    'deadline' => (new DateTime())->modify('+1 month'),
    'subject' => Faker::word(),
    'user' => 'entity|' . \AppBundle\Entity\User::class
]);

$this->define(\SAM\CommonBundle\Entity\SourcingCategory::class)->setDefinitions([
    'name' => Faker::word()
]);

$this->define(\AppBundle\Entity\Sourcing::class)->setDefinitions([
    'company' => 'entity|' . \AppBundle\Entity\Company::class,
    'contact' => 'entity|' . \SAM\AddressBookBundle\Entity\ContactMerged::class
]);

$this->define(\AppBundle\Entity\TimelineEntry::class)->setDefinitions([
    'label' => Faker::word(),
    'date' => Faker::dateTime()
]);

$this->define(\AppBundle\Entity\DealFlowCancelStatus::class)->setDefinitions([
    'status' => Faker::word()
]);

$this->define(\AppBundle\Entity\DealFlowStep::class)->setDefinitions([
    'position' => Faker::randomNumber(1),
    'name' => Faker::company()
]);

$this->define(\AppBundle\Entity\DealFlow::class)->setDefinitions([
    'company' => 'entity|' . \AppBundle\Entity\Company::class,
    'interest' => Faker::numberBetween(1, 5),
    'probability' => Faker::numberBetween(1, 5),
    'type' => \AppBundle\Entity\DealFlow::TYPE_MINORITY,
    'currentStep' => 'entity|' . \AppBundle\Entity\DealFlowStep::class,
    'manager' => 'entity|' . \AppBundle\Entity\User::class,
    'sourcing' => 'entity|' . \AppBundle\Entity\Sourcing::class
])->setCallback(function (\AppBundle\Entity\DealFlow $dealFlow) {
    $dealFlow->addUser($this->create(\AppBundle\Entity\User::class));
});

$this->define(\AppBundle\Entity\InvestorCategory::class)->setDefinitions([
    'name' => Faker::company()
]);

$this->define(\AppBundle\Entity\Board::class)->setDefinitions([
    'name' => Faker::company()
]);

$this->define(\AppBundle\Entity\ShareCategory::class)->setDefinitions([
    'name' => Faker::randomElement(['A', 'B', 'C']),
    'legalEntity' => 'entity|' . \AppBundle\Entity\LegalEntity::class,
    'unitPrice' => Faker::randomFloat(2, 0.2, 10),
]);

$this->define(\AppBundle\Entity\LegalEntity::class)->setDefinitions([
    'name' => Faker::company(),
    'investmentVehicule' => true,
    'fundsRaised' => Faker::randomFloat(2, 50000, 100000),
]);

$this->define(\AppBundle\Entity\Fundraiser::class)->setDefinitions([
    'company' => 'entity|' . \AppBundle\Entity\Company::class,
    'feesAmount' => Faker::randomFloat(2, 200, 500),
    'feesPercentage' => Faker::randomFloat(2, 1, 2),
]);

$this->define(\AppBundle\Entity\InvestorLegalEntityDetails::class)->setDefinitions([
    'shareCategory' => 'entity|' . \AppBundle\Entity\ShareCategory::class,
    // 'investorLegalEntity' => 'entity|' . \AppBundle\Entity\InvestorLegalEntity::class,
    'amount' => Faker::numberBetween(1, 500),
]);

$this->define(\AppBundle\Entity\InvestorLegalEntity::class)->setDefinitions([
    'name' => Faker::company(),
    // 'investor' => 'entity|' . \AppBundle\Entity\Investor::class,
    'legalEntity' => 'entity|' . \AppBundle\Entity\LegalEntity::class,
    'fundraiser' => 'entity|' . \AppBundle\Entity\Fundraiser::class,
    'closing' => Faker::numberBetween(1, 3),
    'warrantSignedAt' => Faker::dateTime(),
    'investmentAmount' => Faker::randomFloat(2, 250, 20000),
    'investmentPercentage' => Faker::randomFloat(2, 2, 18),
    'contactPrimary' => 'entity|' . \SAM\AddressBookBundle\Entity\ContactMerged::class
])->setCallback(function (\AppBundle\Entity\InvestorLegalEntity $investorLegalEntity) {
    $investorLegalEntity->addBoard($this->create(\AppBundle\Entity\Board::class));
    $investorLegalEntityDetails = $this->create(\AppBundle\Entity\InvestorLegalEntityDetails::class);
    $investorLegalEntity->addDetail($investorLegalEntityDetails);
});

$this->define(\AppBundle\Entity\Investor::class)->setDefinitions([
    'company' => 'entity|' . \AppBundle\Entity\Company::class,
    'type' => \SAM\InvestorBundle\Entity\Investor::TYPE_LEGAL_PERSON,
    'creator' => 'entity|' . \AppBundle\Entity\User::class,
    'category' => 'entity|' . \AppBundle\Entity\InvestorCategory::class,
    'totalInvestmentAmount' => Faker::randomFloat(2, 250, 4000),
    'totalInvestmentPercentage' => Faker::randomFloat(2, 2, 18),
])->setCallback(function (\AppBundle\Entity\Investor $investor) {
    $investorLegalEntity = $this->create(\AppBundle\Entity\InvestorLegalEntity::class);
    $investorLegalEntity->setInvestor($investor);
    $investor->addInvestorLegalEntity($investorLegalEntity);
});

// $this->define(\AppBundle\Entity\Notification::class)->setDefinitions([
//     'user' => 'entity|' . \AppBundle\Entity\User::class,
//     'type' => ,
//     'content' => Faker::realText(),
//     'isNew' => true
// ]);