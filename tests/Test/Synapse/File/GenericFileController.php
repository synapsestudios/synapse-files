<?php
namespace Test\Synapse\File;

use Synapse\File\AbstractFileController;

class GenericFileController extends AbstractFileController
{
    public function setSizeRestriction($limit)
    {
        $this->fileSizeRestriction = $limit;
    }

    public function setFileTypeRestrictions(array $types)
    {
        $this->fileTypeRestrictions = $types;
    }
}
