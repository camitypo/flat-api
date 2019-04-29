<?php

/*
 * This file is part of the Symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     */
    public function __construct(FlatService $flatService)
    {
        $this->flatService = $flatService;
    }

    /**
     * Creates new flat resource.
     *
     *
     *
     * @throws ExceptionInterface
     */
    public function post(Request $request): RestResponse
    {
        if (0 === \mb_strlen($request->getContent())) {
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
     *
     *
     * @throws ExceptionInterface
     */
    public function get(string $id): RestResponse
    {
        if (0 === \mb_strlen($id)) {
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
     *
     * @throws ExceptionInterface
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
     *
     * @throws ExceptionInterface
     */
    public function put(Request $request, string $id): RestResponse
    {
        if (0 === \mb_strlen($request->getContent()) || 0 === \mb_strlen($id)) {
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
     */
    public function delete(string $id): RestResponse
    {
        if (0 === \mb_strlen($id)) {
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
}
