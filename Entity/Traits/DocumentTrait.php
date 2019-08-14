<?php

namespace SAM\CommonBundle\Entity\Traits;

use SAM\CommonBundle\Entity\Document;
use Doctrine\Common\Collections\ArrayCollection;

trait DocumentTrait
{
    protected $documents;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param Document $document
     *
     * @return $this
     */
    public function addDocument($document)
    {
        $this->documents[] = $document;

        return $this;
    }

    /**
     * @param Document $document
     *
     * @return $this
     */
    public function removeDocument($document)
    {
        $this->documents->removeElement($document);

        return $this;
    }
}
