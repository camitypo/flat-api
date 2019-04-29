<?php

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
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

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
     *
     * @param FormFactoryInterface   $formFactory
     * @param EntityManagerInterface $entityManager
     * @param FlatRepository         $flatRepository
     * @param MailService            $mailService
     * @param LoggerInterface        $logger
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
     *
     * @param string $data
     *
     * @throws LoaderError
     * @throws RestException
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createFlat(string $data)
    {
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
    }

    /**
     * Updates a single resource or throws 404 error if not found.
     *
     * @param int    $id
     * @param string $data
     *
     * @throws RestException
     */
    public function updateFlat(int $id, string $data)
    {
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
    }

    /**
     * Provides a list containing flat resources found or throws an error 404
     * if no flats are found.
     *
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
     * Gets details of a single flat resource by given uid or throws error 404.
     *
     * @param int $id
     *
     * @throws ExceptionInterface
     * @throws RestException
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
     * @param int $id
     *
     * @throws RestException
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
     * @param FormInterface $form
     * @param string        $data JSON string containing body data
     *
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
