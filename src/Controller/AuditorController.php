<?php

namespace App\Controller;

use App\Repository\AuditorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Auditor;
use App\Entity\Job;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Doctrine\ORM\EntityManagerInterface;

class AuditorController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/api/auditors", methods={"POST"})
     * @Operation(
     *     tags={"Auditors"},
     *     summary="Create a new auditor",
     *     @SWG\Response(
     *         response=201,
     *         description="Returns the newly created auditor",
     *         @Model(type=Auditor::class)
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid request data"
     *     )
     * )
     */
    public function createAuditor(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $auditor = new Auditor();
        $auditor->setName($data['name']);
        $auditor->setLocation($data['location']);
        $auditor->setTimezone($data['timezone']);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($auditor);
        $entityManager->flush();

        return $this->json($auditor, Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/auditors/{id}/schedule", methods={"GET"})
     * @Operation(
     *     tags={"Auditors"},
     *     summary="Get the schedule of an auditor",
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the auditor",
     *         type="integer",
     *         required=true
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returns the auditor's schedule",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(type="string")
     *         )
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Auditor not found"
     *     )
     * )
     */
    public function getSchedule(AuditorRepository $auditorRepository, $id): Response
    {
        $auditor = $auditorRepository->find($id);

        if (!$auditor) {
            return new Response("Auditor not found!", Response::HTTP_NOT_FOUND);
        }

        $schedule = $auditor->getSchedule();

        return $this->json($schedule);
    }

    /**
     * @Route("/api/auditors/{id}/jobs", methods={"POST"})
     */
    public function assignJob(Request $request, $id): Response
    {
        $data = json_decode($request->getContent(), true);

        $auditor = $this->entityManager->getRepository(Auditor::class)->find($id);

        if (!$auditor) {
            return new Response("Auditor not found!", Response::HTTP_NOT_FOUND);
        }

        $job = new Job();
        $job->setTitle($data['title']);
        $job->setDescription($data['description']);
        $job->setDate($data['date']);
        $auditor->addJob($job);

        $this->entityManager->persist($job);
        $this->entityManager->flush();

        return new Response("Job assigned successfully!", Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/jobs/{id}/complete", methods={"PUT"})
     */
    public function markJobAsCompleted(Request $request, $id, SerializerInterface $serializer): Response
    {
        // Retrieve the job from the database
        $job = $this->entityManager->getRepository(Job::class)->find($id);

        if (!$job) {
            return new Response("Job not found", Response::HTTP_NOT_FOUND);
        }

        // Mark the job as completed
        $job->setCompleted(true);

        // If assessment is provided, set it for the job
        $data = json_decode($request->getContent(), true);
        if (isset($data['assessment'])) {
            $job->setAssessment($data['assessment']);
        }

        // Handle time zone conversion based on auditor's location
        $auditor = $job->getAuditor();
        $timezone = new \DateTimeZone($auditor->getTimezone());
        $now = new \DateTime('now', $timezone);
        $job->setCompletedAt($now);

        // Save the changes to the database
        $this->entityManager->flush();

        // Serialize the updated job and return as response
        $json = $serializer->serialize($job, 'json');

        return new Response($json, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}
