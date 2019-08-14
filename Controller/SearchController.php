<?php

namespace SAM\CommonBundle\Controller;

use SAM\AddressBookBundle\Entity\Company;
use SAM\AddressBookBundle\Entity\Contact;
use SAM\AddressBookBundle\Entity\ContactMerged;
use SAM\AddressBookBundle\Repository\CompanyRepositoryInterface;
use SAM\AddressBookBundle\Repository\ContactMergedRepositoryInterface;
use SAM\AddressBookBundle\Repository\ContactRepositoryInterface;
use SAM\AddressBookBundle\Repository\TagRepositoryInterface;
use SAM\AddressBookBundle\Security\ContactVoter;
use SAM\DealFlowBundle\Repository\DealFlowRepositoryInterface;
use SAM\DealFlowBundle\Entity\DealFlow;
use SAM\CommonBundle\Form\Type\SearchType;
use SAM\CommonBundle\Form\Type\UserFilterType;
use SAM\CommonBundle\Manager\PictureManager;
use SAM\InvestorBundle\Entity\InvestorLegalEntity;
use SAM\InvestorBundle\Repository\InvestorLegalEntityRepositoryInterface;
use SAM\SearchBundle\Manager\SearchEngineManager;
use SAM\CommonBundle\Manager\SearchHitManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SearchController extends Controller
{
    /**
     * @param Request        $request
     * @param ObjectManager  $om
     * @param PictureManager $pictureManager
     *
     * @return JsonResponse
     *
     * @Route("/search/users", name="search_users", options={"expose"=true})
     */
    public function searchUser(Request $request, ObjectManager $om, PictureManager $pictureManager)
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $users = null;
            if ($request->query->has('mailPatterns')) {
                $users = $om->getRepository('user')->findByMailPatternsAndQuery($form->get('query')->getData(), $request->query->get('mailPatterns'));
            } else {
                $users = $om->getRepository('user')->findByName($form->get('query')->getData());
            }

            $response = array_map(function (UserInterface $user) use ($pictureManager) {
                $returnArray = [
                    'id'    => $user->getId(),
                    'name'  => $user->getFullName(),
                    'text' => $this->renderView('@SAMCommon/Search/user_result_item.html.twig', [ 'user' => $user ]),
                    'transform' => false,
                    'picture' => $pictureManager->getPicture($user, [ 'imagineFilter' => 'user_contact_thumb_sm' ]),
                    'job' => $user->getJob(),

                ];

                return $returnArray;
            }, $users);

            return new JsonResponse($response);
        }

        return new JsonResponse();
    }

    /**
     * @param Request                       $request
     * @param ObjectManager                 $om
     * @param PictureManager                $pictureManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     *
     * @return JsonResponse
     *
     * @Route("/search/contacts-merged", name="search_contacts_merged", options={"expose"=true})
     */
    public function searchContactMerged(
        Request $request,
        ObjectManager $om,
        PictureManager $pictureManager,
        AuthorizationCheckerInterface $authorizationChecker,
        SearchEngineManager $searchEngineManager,
        SearchHitManager $searchHitManager
    ) {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contacts = $searchEngineManager->getRepository(ContactMergedRepositoryInterface::class)
                ->findContactsMerged(
                    $form->get('query')->getData(),
                    $this->getUser(),
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $this->isGranted('ROLE_CONTACTBOOK_ADMIN')
                );

            $searchHitManager->registerHit($form->get('query')->getData(), 'SAMCommonBundle', 'ContactMerged');

            $response = array_map(function (ContactMerged $contact) use ($pictureManager, $authorizationChecker) {
                $data = [
                    'id'      => $contact->getId(),
                    'name'    => $contact->getFullName(),
                    'text' => $this->renderView('@SAMCommon/Search/contactmerged_result_item.html.twig', [ 'contact' => $contact ]),
                    'picture' => $pictureManager->getPicture($contact, [ 'imagineFilter' => 'user_contact_thumb_sm' ]),
                    'job'     => $contact->getJob(),
                    'transform' => false,
                    'isReadable' => $authorizationChecker->isGranted(ContactVoter::VIEW, $contact)
                ];

                if ($contact->getCompany()) {
                    $data['company'] = [
                        'id' => $contact->getCompany()->getId(),
                        'name' => $contact->getCompany()->getName()
                    ];
                }

                return $data;
            }, is_array($contacts) ? $contacts : $contacts->getItems());

            return new JsonResponse($response);
        }

        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @param ObjectManager $om
     *
     * @param PictureManager $pictureManager
     * @param SearchEngineManager $searchEngineManager
     * @return JsonResponse
     *
     * @Route("/search/companies", name="search_companies", options={"expose"=true})
     */
    public function searchCompanies(Request $request, ObjectManager $om, PictureManager $pictureManager, SearchEngineManager $searchEngineManager, SearchHitManager $searchHitManager)
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $companies = $searchEngineManager->getRepository(CompanyRepositoryInterface::class)->findCompanies(
                $form->get('query')->getData()
            );

            $searchHitManager->registerHit($form->get('query')->getData(), 'SAMCommonBundle', 'Company');

            $response = array_map(function ($company) use ($pictureManager) {
                return [
                    'id'      => $company->getId(),
                    'text' => $this->renderView('@SAMCommon/Search/company_result_item.html.twig', [ 'company' => $company ]),
                    'transform' => false,
                    'picture' => $pictureManager->getPicture($company, [ 'default' => 'company.png', 'imagineFilter' => 'company_thumb_sm', 'fieldName' => 'logoFile' ])
                ];
            }, is_array($companies) ? $companies : $companies->getItems());

            return new JsonResponse($response);
        }

        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @param SearchEngineManager $searchEngineManager
     * @return JsonResponse
     *
     * @Route("/search/jobs", name="search_jobs", options={"expose"=true})
     */
    public function searchJobs(Request $request, SearchEngineManager $searchEngineManager)
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $jobs = $searchEngineManager->getRepository(ContactMergedRepositoryInterface::class)
                ->findJobs($form->get('query')->getData());
            $response = [];
            foreach ($jobs as $job) {
                $response[] = ['id' => $job['job'], 'text' => $job['job']];
            }

            return new JsonResponse($response);
        }

        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @param SearchEngineManager $searchEngineManager
     * @return JsonResponse
     *
     * @Route("/search/unmerged-contacts/jobs", name="search_jobs_unmerged_contacts", options={"expose"=true})
     */
    public function searchJobsUnmergedContacts(Request $request, SearchEngineManager $searchEngineManager)
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $jobs = $searchEngineManager->getRepository(ContactRepositoryInterface::class)
                ->findJobs($form->get('query')->getData());
            $response = [];
            foreach ($jobs as $job) {
                $response[] = ['id' => $job['job'], 'text' => $job['job']];
            }

            return new JsonResponse($response);
        }

        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @param SearchEngineManager $searchEngineManager
     * @return JsonResponse
     *
     * @Route("/search/unmerged-contacts/query", name="search_query_unmerged_contacts", options={"expose"=true})
     */
    public function searchQueryUnmergedContacts(Request $request, SearchEngineManager $searchEngineManager, PictureManager $pictureManager, SearchHitManager $searchHitManager, TranslatorInterface $translator)
    {
        if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_CONTACTBOOK_ADMIN')) {
            $formUser = $this->createForm(UserFilterType::class);
            $formUser->handleRequest($request);
            $userId = $formUser->get('query')->getData();
        } else {
            // If i've got the ROLE_PARTNER, i can only moderate my contact
            $userId = $this->getUser()->getId();
        }

        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $contacts = $searchEngineManager->getRepository(ContactRepositoryInterface::class)
                ->findContacts($form->get('query')->getData(), $userId);

            $searchHitManager->registerHit($form->get('query')->getData(), 'SAMCommonBundle', 'Contact');

            $companies = $searchEngineManager->getRepository(CompanyRepositoryInterface::class)
                ->findCompanies($form->get('query')->getData());
            
            $searchHitManager->registerHit($form->get('query')->getData(), 'SAMCommonBundle', 'Company');

            $response = [];

            if ($contacts->count()) {
                $children = [];
                /** @var Contact $contact */
                foreach ($contacts as $contact) {
                    $children[] = [ 
                        'id' => $contact->getFullName(), 
                        'text' => $this->renderView('@SAMCommon/Search/contact_result_item.html.twig', [ 'contact' => $contact ])
                    ];
                }

                $response[] = [
                    'text' => $translator->trans('text.search.contacts', [], 'SAMCommonBundle'),
                    'children' => $children,
                    'element' => 'HTMLOptGroupElement',
                ];
            }

            if ($companies->count()) {
                $children = [];
                /** @var Company $company */
                foreach ($companies as $company) {
                    $children[] = [
                        'id' => $company->getName(), 
                        'text' => $this->renderView('@SAMCommon/Search/company_result_item.html.twig', [ 'company' => $company ])
                    ];
                }

                $response[] = [
                    'text' => $translator->trans('text.search.companies', [], 'SAMCommonBundle'),
                    'children' => $children,
                    'element' => 'HTMLOptGroupElement',
                ];
            }

            return new JsonResponse($response);
        }

        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @param SearchEngineManager $searchEngineManager
     * @param PictureManager $pictureManager
     * @param SearchHitManager $searchHitManager
     * @return JsonResponse
     *
     * @Route("/search/merged-contacts/query", name="search_query_merged_contacts", options={"expose"=true})
     */
    public function searchQueryMergedContacts(Request $request, SearchEngineManager $searchEngineManager, PictureManager $pictureManager, SearchHitManager $searchHitManager, TranslatorInterface $translator)
    {
        $user = $this->getUser();

        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $contacts = $searchEngineManager->getRepository(ContactMergedRepositoryInterface::class)
                ->findContactsMerged(
                    $form->get('query')->getData(), 
                    $user,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $this->isGranted('ROLE_CONTACTBOOK_ADMIN')
                );

            $searchHitManager->registerHit($form->get('query')->getData(), 'SAMCommonBundle', 'ContactMerged');

            $companies = $searchEngineManager->getRepository(CompanyRepositoryInterface::class)
                ->findCompanies($form->get('query')->getData());
            
            $searchHitManager->registerHit($form->get('query')->getData(), 'SAMCommonBundle', 'Company');

            $response = [];

            if ($contacts->count()) {
                $children = [];
                /** @var Contact $contact */
                foreach ($contacts as $contact) {
                    $children[] = [
                        'id' => $contact->getFullName(), 
                        'text' => $this->renderView('@SAMCommon/Search/contactmerged_result_item.html.twig', [ 'contact' => $contact ])
                    ];
                }

                $response[] = [
                    'text' => $translator->trans('text.search.contacts', [], 'SAMCommonBundle'),
                    'children' => $children,
                    'element' => 'HTMLOptGroupElement',
                ];
            }

            if ($companies->count()) {
                $children = [];
                /** @var Company $company */
                foreach ($companies as $company) {
                    $children[] = [ 
                        'id' => $company->getName(), 
                        'text' => $this->renderView('@SAMCommon/Search/company_result_item.html.twig', [ 'company' => $company ])
                    ];
                }

                $response[] = [
                    'text' => $translator->trans('text.search.companies', [], 'SAMCommonBundle'),
                    'children' => $children,
                    'element' => 'HTMLOptGroupElement',
                ];
            }

            if ($this->getParameter('sam_prospect.finder') && $this->getParameter('sam_prospect.finder')['enable'] 
                && (!$request->query->has('ia-connect') || $request->query->get('ia-connect') === true)) {
                $response[] = [
                    'text' => $this->getParameter('sam_prospect.finder')['name'],
                    'children' => [[
                        'id' => 0,
                        'text' => $this->renderView('@SAMProspect/Finder/select2_item.html.twig', [ 'query' => $form->get('query')->getData() ])
                    ]],
                    'element' => 'HTMLOptGroupElement',
                ];
            }

            return new JsonResponse($response);
        }

        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @param SearchEngineManager $searchEngineManager
     * @return JsonResponse
     *
     * @Route("/search/deal-flow/query", name="search_query_deal_flow", options={"expose"=true})
     */
    public function searchQueryDealFlow(Request $request, SearchEngineManager $searchEngineManager, PictureManager $pictureManager, SearchHitManager $searchHitManager, TranslatorInterface $translator)
    {
        $user = $this->getUser();

        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $deals = $searchEngineManager->getRepository(DealFlowRepositoryInterface::class)
                ->findDeals(['query' => $form->get('query')->getData()], $user);

            $searchHitManager->registerHit($form->get('query')->getData(), 'SAMCommonBundle', 'DealFlow');

            $companies = $searchEngineManager->getRepository(CompanyRepositoryInterface::class)
                ->findCompanies($form->get('query')->getData());

            $searchHitManager->registerHit($form->get('query')->getData(), 'SAMCommonBundle', 'Company');

            $response = [];

            if ($deals->count()) {
                $children = [];
                /** @var DealFlow $deal */
                foreach ($deals as $deal) {
                    $children[] = [
                        'id' => $deal->getProjectName(), 
                        'text' => $this->renderView('@SAMCommon/Search/deal_result_item.html.twig', [ 'deal' => $deal ])
                    ];
                }

                $response[] = [
                    'text' => $translator->trans('text.search.deals', [], 'SAMCommonBundle'),
                    'children' => $children,
                    'element' => 'HTMLOptGroupElement',
                ];
            }

            if ($companies->count()) {
                $children = [];
                /** @var Company $company */
                foreach ($companies as $company) {
                    $children[] = [
                        'id' => $company->getName(), 
                        'text' => $this->renderView('@SAMCommon/Search/company_result_item.html.twig', [ 'company' => $company ])
                    ];
                }

                $response[] = [
                    'text' => $translator->trans('text.search.companies', [], 'SAMCommonBundle'),
                    'children' => $children,
                    'element' => 'HTMLOptGroupElement',
                ];
            }

            return new JsonResponse($response);
        }

        return new JsonResponse();
    }


    /**
     * @param Request $request
     * @param SearchEngineManager $searchEngineManager
     * @param SearchHitManager $searchHitManager
     *
     * @return JsonResponse
     *
     * @Route("/search/tags", name="search_tags", options={"expose"=true})
     */
    public function searchTags(Request $request, SearchEngineManager $searchEngineManager, SearchHitManager $searchHitManager)
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $tags = $searchEngineManager->getRepository(TagRepositoryInterface::class)->findTags(
                $form->get('query')->getData()
            );

            $searchHitManager->registerHit($form->get('query')->getData(), 'SAMCommonBundle', 'Tag');

            $response = array_map(function ($tag) {
                return [
                    'id' => $tag->getId(),
                    'text' => $tag->getName(),
                ];
            }, is_array($tags) ? $tags : $tags->getItems());

            return new JsonResponse($response);
        }

        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @param SearchEngineManager $searchEngineManager
     * @param PictureManager $pictureManager
     * @param SearchHitManager $searchHitManager
     * @return JsonResponse
     *
     * @Route("/search/lps/query", name="search_query_investor_legal_entity", options={"expose"=true})
     */
    public function searchQueryInvestorLegalEntity(Request $request, SearchEngineManager $searchEngineManager, PictureManager $pictureManager, SearchHitManager $searchHitManager, TranslatorInterface $translator)
    {
        $user = $this->getUser();

        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $investorLegalEntities = $searchEngineManager->getRepository(InvestorLegalEntityRepositoryInterface::class)
                ->findInvestorLegalEntities(['query' => $form->get('query')->getData()], $user);

            $searchHitManager->registerHit($form->get('query')->getData(), 'SAMCommonBundle', 'InvestorLegalEntity');

            $companies = $searchEngineManager->getRepository(CompanyRepositoryInterface::class)
                ->findCompanies($form->get('query')->getData());

            $searchHitManager->registerHit($form->get('query')->getData(), 'SAMCommonBundle', 'Company');

            $contacts = $searchEngineManager->getRepository(ContactMergedRepositoryInterface::class)
                ->findContactsMerged(
                    $form->get('query')->getData(), 
                    $user,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $this->isGranted('ROLE_CONTACTBOOK_ADMIN')
                );

            $searchHitManager->registerHit($form->get('query')->getData(), 'SAMCommonBundle', 'ContactMerged');

            $response = [];

            if ($investorLegalEntities->count()) {
                $children = [];
                /** @var InvestorLegalEntity $investorLegalEntity */
                foreach ($investorLegalEntities as $investorLegalEntity) {
                    $children[] = [
                        'id' => $investorLegalEntity->getInvestor()->getName(),
                        'text' => $this->renderView('@SAMCommon/Search/investor_legal_entity_result_item.html.twig', [ 'investorLegalEntity' => $investorLegalEntity ])
                    ];
                }

                $response[] = [
                    'text' => $translator->trans('text.search.lps', [], 'SAMCommonBundle'),
                    'children' => $children,
                    'element' => 'HTMLOptGroupElement',
                ];
            }

            if ($contacts->count()) {
                $children = [];
                /** @var Contact $contact */
                foreach ($contacts as $contact) {
                    $children[] = [
                        'id' => $contact->getFullName(), 
                        'text' => $this->renderView('@SAMCommon/Search/contactmerged_result_item.html.twig', [ 'contact' => $contact ])
                    ];
                }

                $response[] = [
                    'text' => $translator->trans('text.search.contacts', [], 'SAMCommonBundle'),
                    'children' => $children,
                    'element' => 'HTMLOptGroupElement',
                ];
            }

            if ($companies->count()) {
                $children = [];
                /** @var Company $company */
                foreach ($companies as $company) {
                    $children[] = [
                        'id' => $company->getName(),
                        'text' => $this->renderView('@SAMCommon/Search/company_result_item.html.twig', [ 'company' => $company ])
                    ];
                }

                $response[] = [
                    'text' => $translator->trans('text.search.companies', [], 'SAMCommonBundle'),
                    'children' => $children,
                    'element' => 'HTMLOptGroupElement',
                ];
            }

            return new JsonResponse($response);
        }

        return new JsonResponse();
    }
}
