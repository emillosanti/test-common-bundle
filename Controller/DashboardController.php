<?php

namespace SAM\CommonBundle\Controller;

use SAM\CommonBundle\Form\Type\MyAccountType;
use SAM\AddressBookBundle\Form\Type\SearchContactMergedMobileType;
use SAM\AddressBookBundle\Form\Type\SearchContactMergedType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use SAM\CommonBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use FeedIo\Feed;
use FeedIo\FeedIo;

class DashboardController extends Controller
{
    /**
     * @param TokenStorageInterface $tokenStorage
     * @param Request               $request
     * @param ObjectManager         $om
     *
     * @return Response|RedirectResponse
     *
     * @Route("/", name="dashboard_homepage")
     */
    public function homepageAction(TokenStorageInterface $tokenStorage, Request $request, ObjectManager $om)
    {
        $settings = $this->get('settings');
        $bundleCommonSettings = $settings->group('bundle.common');

        if ($bundleCommonSettings && isset($bundleCommonSettings['homepage.enable']) && $bundleCommonSettings['homepage.enable']) {
            return $this->render('@SAMCommon/Dashboard/homepage.html.twig');
        } else {
            return $this->redirectToRoute('edit_user_account');
        }
    }

    public function newsAction(FeedIo $feedIo, $maxResults = 10)
    {
        $feeds = [
            [
                'url' => 'http://plus.lefigaro.fr/tag/private-equity/rss.xml',
                'json' => false,
                'rawTitle' => false,
                'rawDescription' => false,
                'stripTags' => true,
                'getMedias' => true,
                'name' => 'Le Figaro - Private Equity',
                'logo' => 'http://a.f1g.fr/assets-img/i/f/m150.png'
            ],
            // [
            //     'url' => 'http://www.lefigaro.fr/rss/figaro_economie.xml',
            //     'json' => false,
            //     'rawTitle' => false,
            //     'rawDescription' => false,
            //     'stripTags' => true,
            //     'getMedias' => true,
            //     'name' => 'Le Figaro - La Une Eco',
            //     'logo' => 'http://a.f1g.fr/assets-img/i/f/m150.png'
            // ],
            // [
            //     'url' => 'https://business.lesechos.fr/rss/rss_une.xml',
            //     'json' => false,
            //     'rawTitle' => false,
            //     'rawDescription' => false,
            //     'stripTags' => false,
            //     'getMedias' => true,
            //     'name' => 'Les Echos Business',
            //     'logo' => 'https://www.acpm.fr/var/ojd/storage/files/logos/A/logo_7451.jpg',
            // ],
            [
                'url' => 'https://api.rss2json.com/v1/api.json?rss_url=http%3A%2F%2Fwww.pemagazine.fr%2Frss.xml',
                'json' => true,
                'rawTitle' => true,
                'rawDescription' => true,
                'stripTags' => false,
                'getMedias' => true,
                'name' => 'Private Equity Magazine',
                'logo' => 'http://www.pemagazine.fr/img/logo.png'
            ],
            [
                'url' => 'https://api.rss2json.com/v1/api.json?rss_url=https%3A%2F%2Fwww.cfnews.net%2Frss%2Ffeed%2Factualite',
                'json' => true,
                'rawTitle' => true,
                'rawDescription' => true,
                'stripTags' => false,
                'getMedias' => true,
                'name' => 'CFNews',
                'logo' => 'https://docs.cfnews.net/images/CFNEWS-CORPORATE-LOGO.jpg'
            ],
            [
                'url' => 'https://www.fusacq.com/buzz/rss/rss-fusacq-buzz.xml',
                'json' => false,
                'rawTitle' => false,
                'rawDescription' => false,
                'stripTags' => false,
                'name' => 'Fusacq Buzz',
                'getMedias' => false,
                'logo' => 'https://pbs.twimg.com/profile_images/378800000146337999/5b2937aed8df9da2eadf6251d0dfd427_400x400.png'
            ],
        ];

        $news = [];
        foreach ($feeds as $feedInfo) {
            if ($feedInfo['json']) {
                try {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_URL, $feedInfo['url']);
                    $result = curl_exec($ch);
                    curl_close($ch);
                    $json = json_decode($result, true);
                    if ($json['status'] == 'ok') {
                        foreach ($json['items'] as $item) {
                            $news[] = [
                                'title' => $item['title'],
                                'link' => $item['link'],
                                'description' => $feedInfo['stripTags'] ? strip_tags($item['description']) : $item['description'],
                                'medias' => null,
                                'rawDescription' => $feedInfo['rawDescription'],
                                'rawTitle' => $feedInfo['rawTitle'],
                                'date' => new \DateTime($item['pubDate']),
                                'feedName' => $feedInfo['name'],
                                'logo' => $feedInfo['logo']
                            ];
                        }
                    }
                } catch (\Exception $e) {
                }
            } else {
                $feed = $feedIo->read($feedInfo['url'], new Feed)->getFeed();
                foreach ($feed as $item) {
                    $news[] = [
                        'title' => $item->getTitle(),
                        'link' => $item->getLink(),
                        'description' => $feedInfo['stripTags'] ? strip_tags($item->getDescription()) : $item->getDescription(),
                        'medias' => $feedInfo['getMedias'] ? $item->getMedias() : null,
                        'rawTitle' => $feedInfo['rawTitle'],
                        'rawDescription' => $feedInfo['rawDescription'],
                        'date' => $item->getLastModified(),
                        'feedName' => $feedInfo['name'],
                        'logo' => $feedInfo['logo']
                    ];
                }
            }
        }

        usort($news, function($a, $b) {
            return $a['date'] < $b['date'];
        });

        $response = $this->render('@SAMCommon/Dashboard/news.html.twig', [
            'news' => array_slice($news, 0, $maxResults)
        ]);
        $response->setSharedMaxAge($this->getParameter('esi_default_cache_time'));

        return $response;
    }
}
