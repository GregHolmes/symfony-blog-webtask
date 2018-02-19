<?php

namespace App\EventListener\BlogPost;

use App\Entity\BlogPost;
use Doctrine\ORM\Event\LifecycleEventArgs;
use GuzzleHttp\Client as GuzzleClient;

class OnPersistNewBlogPostListener
{
    /**
     * On kernel.controller
     *
     * @param LifecycleEventArgs $args
     *
     * @return void
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // only act on some "BlogPost" entity
        if (!$entity instanceof BlogPost) {
            return;
        }

        $webTaskUrl = getenv('webtaskUrl');
        $authorName = urlencode($entity->getAuthor()->getName());
        $blogTitle = urlencode($entity->getTitle());

        $client = new GuzzleClient();
        $client->get($webTaskUrl . '?authorName=' . $authorName . '&blogTitle=' . $blogTitle);
    }
}