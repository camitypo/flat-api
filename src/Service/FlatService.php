<?php

/*
 * This file is part of the Symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\Flat;
use App\Exception\RestException;
use App\Form\FlatType;
use App\Repository\FlatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class FlatService
{
    /** @var FormFactoryInterface $formFactory */
    private $formFactory;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var FlatRepository $flatRepository */
    private $flatRepository;
    /**
     * @var MailService
     */
    private $mailService;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FlatService constructor.
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        FlatRepository $flatRepository,
        MailService $mailService,
        LoggerInterface $logger
    ) {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->flatRepository = $flatRepository;
        $this->mailService = $mailService;
        $this->logger = $logger;
    }

    /**
     * Creates new flat and persists it.
     *
     * @param $data
     *
     * @throws RestException
     * @throws ExceptionInterface
     */
    public function createFlat(string $data)
    {
        try {
            $flat = new Flat();
            $form = $this->formFactory->create(FlatType::class, $flat);

            if (!$this->processForm($form, $data)) {
                throw new RestException(null, 400);
            }

            // Persist data
            $this->entityManager->persist($form->getData());

            // Flush entity into database
            $this->entityManager->flush();

            $sendStatus = $this->mailService->sendContactEmail($flat);

            if (0 === $sendStatus) {
                $this->logger->error('Email for flat with id '.$flat->getId().'was not send.');
            }
        } catch (Exception $e) {
            $this->logger->error(
                'Error occurred during creation of new flat',
                [
                    'error' => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                    ],
                ]
            );
            throw new RestException($e->getMessage(), 500);
        }
    }

    /**
     * @throws RestException
     * @throws ExceptionInterface
     */
    public function updateFlat(int $id, string $data)
    {
        try {
            $flat = $this->flatRepository->find($id);

            if (!$flat) {
                throw new RestException(null, 404);
            }

            $form = $this->formFactory->create(FlatType::class, $flat);

            if (!$this->processForm($form, $data)) {
                throw new RestException(null, 400);
            }

            $this->entityManager->persist($form->getData());
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->logger->error(
                'Error occurred during flat update',
                [
                    'error' => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                    ],
                ]
            );
            throw new RestException(null, 500);
        }
    }

    /**
     * @throws RestException
     * @throws ExceptionInterface
     *
     * @return string
     */
    public function getFlats()
    {
        $flats = $this->flatRepository->findAll();

        if (!$flats) {
            throw new RestException(null, 404);
        }

        return $this->normalizeData($flats);
    }

    /**
     * @throws RestException
     * @throws ExceptionInterface
     *
     * @return string
     */
    public function getFlat(int $id)
    {
        $flat = $this->flatRepository->find($id);

        if (!$flat) {
            throw new RestException(null, 404);
        }

        return $this->normalizeData($flat);
    }

    /**
     * Deletes single flat resource by given id.
     *
     *
     * @throws RestException
     *
     * @return bool
     */
    public function deleteFlat(int $id)
    {
        $flat = $this->flatRepository->find($id);

        if (!$flat) {
            throw new RestException(null, 404);
        }

        $this->entityManager->remove($flat);
        $this->entityManager->flush();
    }

    /**
     * Processes the form with given data.
     *
     * @param string $data JSON string containing body data
     *
     * @throws ExceptionInterface
     * @throws RestException
     *
     * @return bool
     */
    private function processForm(FormInterface $form, string $data)
    {
        // Decode given data and save it as array
        $data = json_decode($data, true);

        // Throw exception if something went wrong during decoding data.
        if (!$data) {
            throw new RestException(null, 400);
        }

        // Submit form
        $form->submit($data);

        // Return validation result
        return $form->isValid();
    }

    /**
     * Normalizes given data.
     *
     * @param $data
     * @param bool $asJson
     *
     * @throws ExceptionInterface Occurs for all the other cases of errors
     *
     * @return string
     */
    private function normalizeData($data, $asJson = true)
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        if (!$asJson) {
            return $serializer->normalize($data, 'json');
        }

        return $serializer->serialize($serializer->normalize($data, 'json'), 'json');
    }
}
