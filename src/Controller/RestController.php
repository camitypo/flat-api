<?php

namespace App\Controller;

use App\Exception\RestException;
use App\Response\RestResponse;
use App\Service\FlatService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * Provides RESTful CRUD functionality.
 */
class RestController extends AbstractController
{
    /**
     * Service that handles communication between controller and database.
     *
     * @var FlatService
     */
    private $flatService;

    /**
     * RestController constructor.
     * Provides Flat service as dependency injection.
     *
     * @param FlatService $flatService
     */
    public function __construct(FlatService $flatService)
    {
        $this->flatService = $flatService;
    }

    /**
     * Creates new flat resource.
     *
     * @param Request $request
     *
     * @return RestResponse
     */
    public function createFlat(Request $request): RestResponse
    {
        if (!$this->isValidRequest($request->getContent())) {
            return new RestResponse(null, 400);
        }

        try {
            $this->flatService->createFlat($request->getContent());

            return new RestResponse(null, 201);
        } catch (RestException $e) {
            return new RestResponse(null, $e->getCode());
        } catch (Exception $e) {
            return new RestResponse(null, 500);
        }
    }

    /**
     * Provides the details of a single flat resource by given id.
     *
     * @param string $id
     *
     * @throws ExceptionInterface
     *
     * @return RestResponse
     */
    public function getFlat(string $id): RestResponse
    {
        if (!$this->isValidRequest(null, $id, 1)) {
            return new RestResponse(null, 400);
        }

        try {
            $flat = $this->flatService->getFlat((int) $id);

            return new RestResponse($flat, 200);
        } catch (RestException $e) {
            return new RestResponse(null, $e->getCode());
        } catch (Exception $e) {
            return new RestResponse(null, 500);
        }
    }

    /**
     * Provides a list containing all available flat resources.
     *
     * @throws ExceptionInterface
     *
     * @return RestResponse
     */
    public function getList(): RestResponse
    {
        try {
            $flats = $this->flatService->getFlats();

            return new RestResponse($flats, 200);
        } catch (RestException $e) {
            return new RestResponse(null, $e->getCode());
        } catch (Exception $e) {
            return new RestResponse(null, 500);
        }
    }

    /**
     * Updates a flat resource by given id and data.
     *
     * @param Request $request
     * @param string  $id
     *
     * @return RestResponse
     */
    public function updateFlat(Request $request, string $id): RestResponse
    {
        if (!$this->isValidRequest($request->getContent(), $id, 2)) {
            return new RestResponse(null, 400);
        }

        try {
            $this->flatService->updateFlat((int) $id, $request->getContent());

            return new RestResponse(null, 200);
        } catch (RestException $e) {
            return new RestResponse(null, $e->getCode());
        } catch (Exception $e) {
            return new RestResponse(null, 500);
        }
    }

    /**
     * Deletes a flat resource by given id.
     *
     * @param string $id
     *
     * @return RestResponse
     */
    public function deleteFlat(string $id): RestResponse
    {
        if (!$this->isValidRequest(null, $id, 1)) {
            return new RestResponse(null, 400);
        }

        try {
            $this->flatService->deleteFlat((int) $id);

            return new RestResponse(null, 200);
        } catch (RestException $e) {
            return new RestResponse(null, $e->getCode());
        } catch (Exception $e) {
            return new RestResponse(null, 500);
        }
    }

    /**
     * Checks if request contains required payload and parameters depending on
     * given check case.
     *
     * @param string|null $payload
     * @param int|null    $id
     * @param int         $checkCase
     *
     * @return bool
     */
    private function isValidRequest($payload = null, $id = null, $checkCase = 0)
    {
        switch ($checkCase) {
            case 1:
                return 0 !== mb_strlen($id);
                break;
            case 2:
                return (0 !== mb_strlen($payload)) && (0 !== mb_strlen($id));
                break;
            default:
                return 0 !== mb_strlen($payload);
                break;
        }
    }
}
