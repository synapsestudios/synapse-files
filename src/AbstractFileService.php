<?php
namespace Synapse\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFileService implements FileServiceInterface
{
    /**
     * @param Request            $request
     * @param AbstractFileMapper $fileMapper
     *
     * @return AbstractFileEntity
     * @throws FileException
     */
    public function create(Request $request, AbstractFileMapper $fileMapper)
    {
        $files = $request->files;
        if ($files->count() == 0) {
            throw new FileException('No file uploaded', 400);
        } elseif ($files->count() > 1) {
            throw new FileException('Only one file can be uploaded to this endpoint', 400);
        }
        /** @var UploadedFile $file */
        $file = $files->getIterator()->current();
        if ($file->getError() > 0) {
            throw new FileException('File did not upload correctly', 500);
        }

        $data = [
            'file_name'    => $file->getClientOriginalName(),
            'content_type' => $file->getMimeType() ?: $file->getClientMimeType(),
            'size'         => $file->getSize(),
        ];

        $class = get_class($fileMapper->getPrototype());

        /** @var AbstractFileEntity $entity */
        $entity = new $class();
        $entity->exchangeArray($data);
        $entity = $fileMapper->insert($entity);

        try {
            $this->save(
                $entity->getPath(),
                fopen($file->getRealPath(), 'r'),
                $file->getMimeType()
            );
        } catch (\Exception $e) {
            $fileMapper->delete($entity);
            throw new FileException('Error saving file', 500);
        }

        return $entity;
    }
}
