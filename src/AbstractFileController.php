<?php
namespace Synapse\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Synapse\Controller\AbstractRestController;

abstract class AbstractFileController extends AbstractRestController
{
    const ERROR_NO_FILE_UPLOADED      = 'No file uploaded';
    const ERROR_FILE_SIZE_EXCEEDED    = 'File size exceeded';
    const ERROR_FILE_TYPE_NOT_ALLOWED = 'File type not allowed';

    /**
     * @var FileServiceInterFace
     */
    protected $fileService;

    /**
     * @var AbstractFileMapper
     */
    protected $fileMapper;

    /**
     * File size limitation for upload
     * Zero = unlimited
     * @var int
     */
    protected $fileSizeRestriction = 0;

    /**
     * File type (mime type) limitations
     * @var array
     */
    protected $fileTypeRestrictions = [];

    public function __construct(
        FileServiceInterface $fileService,
        AbstractFileMapper $fileMapper
    ) {
        $this->fileService = $fileService;
        $this->fileMapper  = $fileMapper;
    }

    public function post(Request $request)
    {
        try {
            if ($request->files->count() != 1) {
                return $this->createErrorResponse(self::ERROR_NO_FILE_UPLOADED, 400);
            }

            // check file restrictions
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $request->files->getIterator()->current();
            if ($this->fileSizeRestriction && $this->fileSizeRestriction < $uploadedFile->getSize()) {
                return $this->createErrorResponse(self::ERROR_FILE_SIZE_EXCEEDED, 400);
            }
            if (!empty($this->fileTypeRestrictions)
                && !in_array($uploadedFile->getMimeType(), $this->fileTypeRestrictions)) {
                return $this->createErrorResponse(self::ERROR_FILE_TYPE_NOT_ALLOWED, 400);
            }

            $file = $this->fileService->create($request, $this->fileMapper);
        } catch (FileException $e) {
            $this->logger->error($e->getMessage(), ['files' => $request->files->all()]);

            return $this->createSimpleResponse($e->getCode() ?: 500, $e->getMessage());
        }

        $response = $this->createEntityResponse($file, 201);

        return $response;
    }

    protected function isRequestForDownload(Request $request)
    {
        return $request->get('download') ? true : false;
    }

    public function get(Request $request, $fileEntity)
    {
        if (!$fileEntity) {
            return $this->createNotFoundResponse();
        }

        if ($this->isRequestForDownload($request)) {
            $disposition = 'attachment';
        } else {
            $disposition = 'inline';
        }
        $fileName = $fileEntity->getFileName();

        $headers = [
            'Content-Type'        => $fileEntity->getContentType(),
            'Content-Disposition' => "{$disposition}; filename={$fileName}",
            'Content-Length'      => intval($fileEntity->getSize()),
        ];

        $resource = $this->fileService->loadFile($fileEntity);
        $stream = function () use ($resource) {
            fpassthru($resource);
        };

        return new StreamedResponse($stream, 200, $headers);
    }

    /**
     * @param Request            $request
     * @param AbstractFileEntity $fileEntity
     *
     * @return bool|\Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, $fileEntity)
    {
        if (!$fileEntity) {
            return $this->createNotFoundResponse();
        }

        return $this->fileService->delete($fileEntity->getPath());
    }
}
