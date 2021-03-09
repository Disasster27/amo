<?php

namespace src\services;

use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Collections\TasksCollection;
use AmoCRM\Models\TaskModel;
use AmoCRM\Client\AmoCRMApiClient;

class CrmHandlerService
{
    public function getContacts($apiClient): array
    {
        $contacts = $apiClient->contacts()->get(null, ['leads']);
        return $contacts->toArray();
    }

    public function addTasks($contacts, $apiClient)
    {
        foreach ($contacts as $contact) {
            if ($contact['leads'] === null) {
                $this->createTask($contact['id'], $apiClient);
            }
        }
    }

    private function createTask($contactId, $apiClient)
    {
        //Создадим задачу
        $tasksCollection = new TasksCollection();
        $task = new TaskModel();
        $task->setTaskTypeId(TaskModel::TASK_TYPE_ID_CALL)
            ->setText('Контакт без сделок')
            ->setCompleteTill(mktime(10, 0, 0, 10, 3, 2021))
            ->setEntityType(EntityTypesInterface::CONTACTS)
            ->setEntityId($contactId);
        $tasksCollection->add($task);
        
        try {
            $tasksCollection = $apiClient->tasks()->add($tasksCollection);
        } catch (AmoCRMApiException $e) {
            printf($e);
            die;
        }
    }
}