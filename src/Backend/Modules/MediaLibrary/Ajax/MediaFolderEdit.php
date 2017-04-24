<?php

namespace Backend\Modules\MediaLibrary\Ajax;

use Backend\Core\Engine\Base\AjaxAction as BackendBaseAJAXAction;
use Backend\Core\Language\Language;
use Backend\Modules\MediaLibrary\Domain\MediaFolder\Command\UpdateMediaFolder;
use Backend\Modules\MediaLibrary\Domain\MediaFolder\MediaFolder;
use Common\Exception\AjaxExitException;
use Common\Uri;

/**
 * This edit-action will update a folder using AJAX
 */
class MediaFolderEdit extends BackendBaseAJAXAction
{
    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        /** @var UpdateMediaFolder $updateMediaFolder */
        $updateMediaFolder = $this->updateMediaFolder();

        // Output
        $this->output(
            self::OK,
            $updateMediaFolder->getMediaFolderEntity(),
            sprintf(
                Language::msg('MediaFolderIsEdited'),
                $updateMediaFolder->getMediaFolderEntity()->getName()
            )
        );
    }

    /**
     * @return MediaFolder
     * @throws AjaxExitException
     */
    protected function getMediaFolder(): MediaFolder
    {
        $id = $this->get('request')->request->getInt('folder_id');

        // validate values
        if ($id === null) {
            throw new AjaxExitException(Language::err('FolderIdIsRequired'));
        }

        try {
            /** @var MediaFolder $mediaFolder */
            return $this->get('media_library.repository.folder')->findOneById($id);
        } catch (\Exception $e) {
            throw new AjaxExitException(Language::err('MediaFolderDoesNotExists'));
        }
    }

    /**
     * @return string
     * @throws AjaxExitException
     */
    protected function getFolderName(): string
    {
        $name = $this->get('request')->request->get('name');

        if ($name === null) {
            throw new AjaxExitException(Language::err('TitleIsRequired'));
        }

        return Uri::getUrl($name);
    }

    /**
     * @return UpdateMediaFolder
     */
    private function updateMediaFolder()
    {
        /** @var MediaFolder $mediaFolder */
        $mediaFolder = $this->getMediaFolder();

        /** @var string $name */
        $name = $this->getFolderName();

        /** @var UpdateMediaFolder $updateMediaFolder */
        $updateMediaFolder = new UpdateMediaFolder($mediaFolder);
        $updateMediaFolder->name = htmlspecialchars($name, ENT_QUOTES);

        // Handle the MediaFolder update
        $this->get('command_bus')->handle($updateMediaFolder);

        return $updateMediaFolder;
    }
}
