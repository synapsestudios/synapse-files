<?php
namespace Synapse\File;

abstract class AbstractDocumentFileController extends AbstractFileController
{
    // 5MB
    protected $fileSizeRestriction  = 5242880;
    protected $fileTypeRestrictions = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'application/x-pdf',
        'application/vnd.pdf',
    ];

    /**
     * @param $type
     * @codeCoverageIgnore
     */
    public function addFileTypeRestriction($type)
    {
        $this->fileTypeRestrictions[] = $type;
    }
}
