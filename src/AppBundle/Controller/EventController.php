<?php

namespace AppBundle\Controller;

use AppBundle\Controller\Infrastructure\RestController;
use AppBundle\Entity\Event;
use AppBundle\Entity\Image;
use AppBundle\Entity\Repository\EventRepository;
use AppBundle\Entity\Repository\ImageRepository;
use AppBundle\Form\Event\EventFormType;
use AppBundle\Form\Event\LinksCollectionFormType;
use AppBundle\Response\ApiError;
use AppBundle\Response\ApiValidationError;
use AppBundle\Response\CollectionApiResponse;
use AppBundle\Response\EmptyApiResponse;
use AppBundle\Response\Infrastructure\AbstractApiResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Vehsamrak
 * @Route("event")
 */
class EventController extends RestController
{

    /**
     * Find events by name part
     * @Route("s/like/{searchString}/{limit}/{offset}", name="events_find_like")
     * @Method("GET")
     * @ApiDoc(
     *     section="Event",
     *     statusCodes={
     *         200="OK",
     *     }
     * )
     * @param string $searchString Search string
     * @param int $limit Limit results. Default is 50
     * @param int $offset Starting serial number of result collection. Default is 0
     */
    public function findLikeAction($searchString = null, $limit = 50, $offset = null)
    {
        $eventRepository = $this->get('rockparade.event_repository');
        $events = $eventRepository->findLike($searchString);
        $total = $events->count();

        $limit = (int) filter_var($limit, FILTER_VALIDATE_INT);
        $offset = (int) filter_var($offset, FILTER_VALIDATE_INT);

        $events = $events->slice($offset, $limit ?: null);

        $response = new CollectionApiResponse(
            $events,
            Response::HTTP_OK,
            $total,
            $limit,
            $offset
        );

        return $this->respond($response);
    }

    /**
     * List all events
     * @Route("s/{limit}/{offset}", name="events_list")
     * @Method("GET")
     * @ApiDoc(
     *     section="Event",
     *     statusCodes={
     *         200="OK",
     *     }
     * )
     * @param int $limit Limit results. Default is 50
     * @param int $offset Starting serial number of result collection. Default is 0
     */
    public function listAction($limit = null, $offset = null): Response
    {
        return $this->listEntities($this->get('rockparade.event_repository'), $limit, $offset);
    }

    /**
     * View event by id
     * @Route("/{id}", name="event_view")
     * @Method("GET")
     * @ApiDoc(
     *     section="Event",
     *     statusCodes={
     *         200="Event was found",
     *         404="Event with given id was not found",
     *     }
     * )
     * @param string $id event id
     */
    public function viewAction(string $id): Response
    {
        return $this->viewEntity($this->get('rockparade.event_repository'), $id);
    }

    /**
     * Create new event
     * @Route("")
     * @Method("POST")
     * @Security("has_role('ROLE_USER')")
     * @ApiDoc(
     *     section="Event",
     *     requirements={
     *         {
     *             "name"="name",
     *             "dataType"="string",
     *             "requirement"="true",
     *             "description"="event name"
     *         },
     *         {
     *             "name"="date",
     *             "dataType"="date (dd-MM-yyyy HH:mm)",
     *             "requirement"="true",
     *             "description"="event date"
     *         },
     *         {
     *             "name"="description",
     *             "dataType"="text",
     *             "requirement"="true",
     *             "description"="event description"
     *         },
     *         {
     *             "name"="place",
     *             "dataType"="text",
     *             "requirement"="true",
     *             "description"="event place"
     *         },
     *     },
     *     statusCodes={
     *         201="New event was created. Link to new resource in header 'Location'",
     *         400="Validation error",
     *         401="Authentication required",
     *     }
     * )
     */
    public function createAction(Request $request): Response
    {
        $response = $this->createOrUpdateEvent($request);

        return $this->respond($response);
    }

    /**
     * Edit event
     * @Route("/{id}", name="event_edit")
     * @Method("PUT")
     * @Security("has_role('ROLE_USER')")
     * @ApiDoc(
     *     section="Event",
     *     requirements={
     *         {
     *             "name"="name",
     *             "dataType"="string",
     *             "requirement"="true",
     *             "description"="event name"
     *         },
     *         {
     *             "name"="date",
     *             "dataType"="date (dd-MM-yyyy HH:mm)",
     *             "requirement"="true",
     *             "description"="event date"
     *         },
     *         {
     *             "name"="description",
     *             "dataType"="string",
     *             "requirement"="true",
     *             "description"="event description"
     *         },
     *         {
     *             "name"="place",
     *             "dataType"="text",
     *             "requirement"="true",
     *             "description"="event place"
     *         },
     *     },
     *     statusCodes={
     *         204="Event was edited with new data",
     *         400="Validation error",
     *         401="Authentication required",
     *         404="Event with given id was not found",
     *     }
     * )
     * @param string $id event id
     */
    public function editAction(Request $request, string $id): Response
    {
        $response = $this->createOrUpdateEvent($request, $id);

        return $this->respond($response);
    }

    /**
     * Delete event
     * @Route("/{id}", name="event_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_USER')")
     * @ApiDoc(
     *     section="Event",
     *     statusCodes={
     *         204="Event was deleted",
     *         401="Authentication required",
     *         403="Only event creator can delete event",
     *         404="Event with given id was not found",
     *     }
     * )
     * @param string $id event id
     */
    public function deleteEvent(string $id): Response
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->get('rockparade.event_repository');
        /** @var Event $event */
        $event = $eventRepository->findOneById($id);

        if ($event) {
            if ($event->getCreator() === $this->getUser()) {
                $eventRepository->remove($event);
                $eventRepository->flush();

                $response = new EmptyApiResponse(Response::HTTP_NO_CONTENT);
            } else {
                $response = new ApiError('Only event creator can delete event.', Response::HTTP_FORBIDDEN);
            }
        } else {
            $eventService = $this->get('rockparade.event');
            $response = $eventService->createEventNotFoundErrorResult($id);
        }

        return $this->respond($response);
    }

    /**
     * Add image to event
     * @Route("/{id}/image", name="event_image_add")
     * @Method("POST")
     * @Security("has_role('ROLE_USER')")
     * @ApiDoc(
     *     section="Event",
     *     statusCodes={
     *         200="OK",
     *         401="Authentication required",
     *         403="Only event creator can add images",
     *         404="Event with given id was not found",
     *     }
     * )
     * @param string $id event id
     */
    public function addImageAction(Request $request, string $id): Response
    {
        $eventService = $this->get('rockparade.event');
        $response = $eventService->addImageToEvent($id, $this->getUser(), $request->get('image'));

        return $this->respond($response);
    }

    /**
     * Get event image
     * @Route("/{id}/image/{imageName}", name="event_image_view")
     * @Method("GET")
     * @ApiDoc(
     *     section="Event",
     *     statusCodes={
     *         200="OK",
     *         404="Event with given id was not found",
     *         404="Image with given name was not found",
     *     }
     * )
     * @param string $id event id
     * @param string $imageName image name
     */
    public function viewImageAction(string $id, string $imageName): Response
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->get('rockparade.event_repository');
        $event = $eventRepository->findOneById($id);
        $entityService = $this->get('rockparade.entity_service');

        if ($event) {
            $image = $event->getImageWithName($imageName);
            $apiResponseFactory = $this->get('rockparade.api_response_factory');

            if ($image) {
                $response = $apiResponseFactory->createImageResponse($image);
            } else {
                $response = $entityService->createEntityNotFoundResponse(Image::class, $imageName);
            }
        } else {
            $response = $entityService->createEntityNotFoundResponse(Event::class, $id);
        }

        return $this->respond($response);
    }

    /**
     * Delete event image
     * @Route("/{id}/image/{imageId}", name="event_image_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_USER')")
     * @ApiDoc(
     *     section="Event",
     *     statusCodes={
     *         200="OK",
     *         401="Authentication required",
     *         403="Only event creator can delete images",
     *         404="Event with given id was not found",
     *         404="Image with given id was not found",
     *     }
     * )
     * @param string $id event id
     * @param string $imageId image id
     */
    public function deleteImageAction(string $id, string $imageId)
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->get('rockparade.event_repository');
        /** @var ImageRepository $imageRepository */
        $imageRepository = $this->get('rockparade.image_repository');
        $event = $eventRepository->findOneById($id);
        $entityService = $this->get('rockparade.entity_service');

        if ($event) {
            if ($this->getUser()->getLogin() !== $event->getCreator()->getLogin()) {
                $response = new ApiError('Only event creator can delete images.', Response::HTTP_FORBIDDEN);
            } else {
                $image = $imageRepository->findOneById($imageId);

                if ($image) {
                    $event->removeImage($image);
                    $eventRepository->flush();
                    $response = new EmptyApiResponse(Response::HTTP_OK);
                } else {
                    $response = $entityService->createEntityNotFoundResponse(Image::class, $imageId);
                }
            }
        } else {
            $response = $entityService->createEntityNotFoundResponse(Event::class, $id);
        }

        return $this->respond($response);
    }

    /**
     * Add links to event
     * @Route("/{id}/links", name="event_links_add")
     * @Method("POST")
     * @Security("has_role('ROLE_USER')")
     * @ApiDoc(
     *     section="Event",
     *     requirements={
     *         {
     *             "name"="links",
     *             "dataType"="array",
     *             "requirement"="true",
     *             "description"="list of links"
     *         },
     *         {
     *             "name"="links[0][url]",
     *             "dataType"="string",
     *             "requirement"="true",
     *             "description"="link url"
     *         },
     *         {
     *             "name"="links[0][description]",
     *             "dataType"="string",
     *             "requirement"="false",
     *             "description"="link description"
     *         },
     *     },
     *     statusCodes={
     *         201="Link created and added to event",
     *         400="Links must have unique url",
     *         401="Authentication required",
     *         403="Only event creator can add links",
     *         404="Event with given id was not found",
     *     }
     * )
     * @param string $id event id
     */
    public function addLinksAction(Request $request, string $id): Response
    {
        $eventService = $this->get('rockparade.event');

        $form = $this->createForm(LinksCollectionFormType::class);
        $this->processForm($request, $form);

        $response = $eventService->addLinksToEvent($id, $this->getUser(), $form);

        return $this->respond($response);
    }

    /**
     * Delete link from event
     * @Route("/{id}/link/{linkId}", name="event_link_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_USER')")
     * @ApiDoc(
     *     section="Event",
     *     statusCodes={
     *         200="OK",
     *         401="Authentication required",
     *         403="Only event creator can delete links",
     *         404="Event with given id was not found",
     *         404="Link with given id was not found",
     *     }
     * )
     * @param string $id event id
     * @param string $linkId link id
     */
    public function deleteLinkAction(string $id, string $linkId)
    {
        $eventService = $this->get('rockparade.event');
        $response = $eventService->removeLinksFromEvent($id, $linkId, $this->getUser());

        return $this->respond($response);
    }

    private function createOrUpdateEvent(Request $request, string $id = null): AbstractApiResponse
    {
        $form = $this->createForm(EventFormType::class);
        $this->processForm($request, $form);

        if ($form->isValid()) {
            $eventService = $this->get('rockparade.event');
            $id = (string) $id;

            if ($id) {
                $response = $eventService->editEventByForm($form, $id);
            } else {
                $response = $eventService->createEventByForm($form, $this->getUser());
            }
        } else {
            $response = new ApiValidationError($form);
        }

        return $response;
    }
}
