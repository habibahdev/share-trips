<?php

namespace App\DataFixtures;

use App\Entity\Booking;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\Payment;
use App\Entity\Review;
use App\Entity\Trip;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Enum\BookingStatus;
use App\Enum\PaymentMethod;
use App\Enum\PaymentStatus;
use App\Enum\TripStatus;
use App\Enum\UserStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const CITIES = [
        'Paris', 'Lyon', 'Marseille', 'Bordeaux',
        'Toulouse', 'Nantes', 'Strasbourg',
        'Lille', 'Rennes', 'Nice', 'Angers', 'Rouen'
    ];

    private const VEHICLES = [
        ['Renault', 'Clio', '5 places', 5, 'AB-123-CD'],
        ['Peugeot', '308', '5 places', 5, 'EF-456-GH'],
        ['Toyota', 'Yaris', '5 places', 5, 'IJ-789-KL'],
        ['Citroën', 'C3', '5 places', 5, 'MN-012-OP'],
        ['Ford', 'Focus', '5 places', 5, 'QR-345-ST'],
        ['Dacia', 'Sandero', '5 places', 5, 'UV-678-WX'],
        ['BMW', 'Série 3', '5 places', 5, 'YZ-901-AB'],
        ['Audi', 'A3', '5 places', 5, 'CD-234-EF'],
        ['Volvo', 'XC40', '5 places', 5, 'GH-567-IJ'],
        ['Tesla', 'Model 3', '5 places', 5, 'KL-890-MN'],
    ];

    private const REVIEWS = [
        [5, 'Trajet très agréable, conducteur ponctuel et sympathique !'],
        [4, 'Bonne expérience, je recommande.'],
        [3, 'Trajet correct, rien à signaler.'],
        [2, 'Conducteur en retard de 20 minutes, peu communicatif. Décevant.'],
        [1, 'Très mauvaise expérience, véhicule sale et conduite dangereuse. À éviter.']
    ];

    private const CONVERSATIONS = [
        [
            ['sender' => 'driver',
                'content' => 'Bonjour ! Votre réservation est bien enregistrée. N\'hésitez pas à me contacter ici.'],
            ['sender' => 'passenger', 'content' => 'Merci ! Où est-ce qu\'on se retrouve exactement ?'],
            ['sender' => 'driver',
                'content' => 'On se retrouve devant la gare principale, côté place du Général de Gaulle.'],
            ['sender' => 'passenger', 'content' => 'Parfait, je serai là 5 minutes avant. Merci !'],
            ['sender' => 'driver',    'content' => 'Super, à bientôt !'],
        ],
        [
            ['sender' => 'driver',
                'content' => 'Bonjour ! Votre réservation est bien enregistrée. N\'hésitez pas à me contacter ici.'],
            ['sender' => 'passenger', 'content' => 'Bonjour, est-ce qu\'il y a de la place pour un bagage en soute ?'],
            ['sender' => 'driver', 'content' => 'Oui bien sûr, j\'ai un grand coffre. Pas de problème pour un bagage.'],
            ['sender' => 'passenger', 'content' => 'Super, merci beaucoup !'],
        ],
        [
            ['sender' => 'driver',
                'content' => 'Bonjour ! Votre réservation est bien enregistrée. N\'hésitez pas à me contacter ici.'],
            ['sender' => 'passenger', 'content' => 'Bonjour, vous acceptez les animaux de compagnie ?'],
            ['sender' => 'driver', 'content' => 'Désolé, je préfère éviter les animaux dans le véhicule.'],
            ['sender' => 'passenger', 'content' => 'D\'accord, pas de problème. Je ferai garder mon chat.'],
            ['sender' => 'driver', 'content' => 'Merci de votre compréhension !'],
            ['sender' => 'passenger', 'content' => 'À samedi alors !'],
        ],
        [
            ['sender' => 'driver',
                'content' => 'Bonjour ! Votre réservation est bien enregistrée. N\'hésitez pas à me contacter ici.'],
            ['sender' => 'passenger',
                'content' => 'Bonjour, est-ce que vous pouvez me déposer à la gare en arrivant ?'],
            ['sender' => 'driver', 'content' => 'Oui c\'est sur mon chemin, aucun problème !'],
        ],
        [
            ['sender' => 'driver',
                'content' => 'Bonjour ! Votre réservation est bien enregistrée. N\'hésitez pas à me contacter ici.'],
            ['sender' => 'passenger', 'content' => 'Bonjour ! Je voulais juste confirmer l\'heure de départ.'],
            ['sender' => 'driver', 'content' => 'On part à l\'heure prévue, 8h30 pile.'],
            ['sender' => 'passenger', 'content' => 'Nickel, je serai là. Bonne soirée !'],
            ['sender' => 'driver', 'content' => 'Bonne soirée à vous aussi !'],
        ],
    ];

    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('contact@sharetrips.fr')
             ->setFirstName('Emmanuelle')
             ->setLastName('Dubernard')
             ->setIsVerified(true)
             ->setRoles(['ROLE_ADMIN'])
             ->setStatus(UserStatus::Active)
             ->setPassword($this->hasher->hashPassword($admin, 'admin23'));
        $manager->persist($admin);

        $users = [];
        $firstNames = ['Alice', 'Baptiste', 'Clara', 'David', 'Emma'];
        $lastNames = ['Martin', 'Bernard', 'Dupont', 'Leroy', 'Moreau'];

        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail(sprintf("user%d@sharetrips.fr", $i + 1))
                 ->setFirstName($firstNames[$i])
                 ->setLastName($lastNames[$i])
                 ->setIsVerified(true)
                 ->setRoles(['ROLE_USER'])
                 ->setStatus(UserStatus::Active)
                 ->setPassword($this->hasher->hashPassword($user, 'password'));
            $manager->persist($user);
            $users[] = $user;
        }

        $vehicles = [];
        foreach ($users as $index => $user) {
            $vehicleCount = ($index % 2 === 0) ? 2 : 1;
            for ($v = 0; $v < $vehicleCount; $v++) {
                $vehicleData = self::VEHICLES[($index * 2 + $v) % count(self::VEHICLES)];
                $vehicle = new Vehicle();
                $vehicle->setUsser($user)
                        ->setBrand($vehicleData[0])
                        ->setModel($vehicleData[1])
                        ->setSeats($vehicleData[3])
                        ->setPlate($vehicleData[4] . '-' . $index . $v);
                $manager->persist($vehicle);
                $vehicles[$index][] = $vehicle;
            }
        }

        $trips = [];

        for ($i = 0; $i < 3; $i++) {
            $driver = $users[$i];
            $vehicle = $vehicles[$i][0];

            $tripPast = $this->createTrip(
                driver: $driver,
                vehicle: $vehicle,
                origin: self::CITIES[$i],
                dest: self::CITIES[$i + 1],
                departure: new \DateTimeImmutable(sprintf('-%d days', $i + 2)),
                seats: 3,
                price: 12.0 + $i,
                status: TripStatus::Completed
            );

            $manager->persist($tripPast);

            $trips[] = [
                'trip' => $tripPast,
                'driver' => $driver,
                'type' => 'past'
            ];
        }

        for ($i = 0; $i < 12; $i++) {
            $driver = $users[$i % count($users)];
            $vehicle = $vehicles[$i % count($users)][0];

            $tripOpen = $this->createTrip(
                driver: $driver,
                vehicle: $vehicle,
                origin: self::CITIES[$i % count(self::CITIES)],
                dest: self::CITIES[($i + 2) % count(self::CITIES)],
                departure: new \DateTimeImmutable(sprintf('+%d days', $i + 1)),
                seats: rand(2, 5),
                price: 10.0 + $i,
                status: TripStatus::Open
            );

            $manager->persist($tripOpen);

            $trips[] = [
                'trip' => $tripOpen,
                'driver' => $driver,
                'type' => 'open'
            ];
        }

        $manager->flush();
        foreach ($trips as $tripData) {
            /** @var Trip $trip */
            $trip = $tripData['trip'];
            $driver = $tripData['driver'];
            $type = $tripData['type'];

            $passengers = array_filter(
                $users,
                fn(User $u) => $u->getId() !== $driver->getId()
            );
            $passengers = array_values($passengers);

            match ($type) {
                'open' => $this->createOpenTripBookings(
                    $manager,
                    $trip,
                    $passengers
                ),
                'past' => $this->createPastTripBookings(
                    $manager,
                    $trip,
                    $passengers,
                    $driver
                ),
            };
        }

        $manager->flush();

        // conversations sur les trajets ouverts
        $conversationIndex = 0;
        foreach ($trips as $tripData) {
            if ($tripData['type'] !== 'open') {
                continue;
            }
            /** @var Trip $trip */
            $trip = $tripData['trip'];
            $driver = $tripData['driver'];

            $passengers = array_values(array_filter($users, fn(User $u) => $u->getId() !== $driver->getId()));

            // conversation par trajet ouvert
            $passenger = $passengers[0];
            $messages = self::CONVERSATIONS[$conversationIndex % count(self::CONVERSATIONS)];

            $this->createConversation(
                manager: $manager,
                trip: $trip,
                driver: $driver,
                passenger: $passenger,
                messages: $messages,
                baseTime: new \DateTimeImmutable(sprintf('-%d hours', rand(1, 48)))
            );
            $conversationIndex++;
        }

        // conversation sur trajets passés
        foreach ($trips as $tripData) {
            if ($tripData['type'] !== 'open') {
                continue;
            }
            /** @var Trip $trip */
            $trip = $tripData['trip'];
            $driver = $tripData['driver'];

            $passengers = array_values(array_filter($users, fn(User $u) => $u->getId() !== $driver->getId()));

            // conversation par trajet ouvert
            foreach (array_slice($passengers, 0, 2) as $passenger) {
                $messages = self::CONVERSATIONS[$conversationIndex % count(self::CONVERSATIONS)];
                $this->createConversation(
                    manager: $manager,
                    trip: $trip,
                    driver: $driver,
                    passenger: $passenger,
                    messages: $messages,
                    baseTime: new \DateTimeImmutable(sprintf('-%d hours', rand(1, 48)))
                );
                $conversationIndex++;
            }
        }
        $manager->flush();
    }

    /**
     * @param array<array{sender: string, content: string}> $messages
     */
    private function createConversation(
        ObjectManager $manager,
        Trip $trip,
        User $driver,
        User $passenger,
        array $messages,
        \DateTimeImmutable $baseTime
    ): void {
        $conversation = new Conversation();
        $conversation->setTrip($trip)
            ->setDriver($driver)
            ->setPassenger($passenger)
            ->setCreatedAt($baseTime);
        $manager->persist($conversation);

        foreach ($messages as $i => $messageData) {
            $sender = $messageData['sender'] === 'driver' ? $driver : $passenger;
            $sentAt = $baseTime->modify(sprintf('+%d minutes', $i * rand(2, 15)));
            $isLastPassengerMsg = (
                $messageData['sender'] === 'passenger' && $i === array_key_last($messages)
            );

            $message = new Message();
            $message->setConversation($conversation)
                ->setSender($sender)
                ->setContent($messageData['content'])
                ->setSentAt($sentAt)
                ->setIsRead(!$isLastPassengerMsg);
            $manager->persist($message);
        }
    }

    /**
     * @param User[] $passengers
     */
    private function createOpenTripBookings(
        ObjectManager $manager,
        Trip $trip,
        array $passengers
    ): void {
        // Réservation confirmée
        $booking1 = $this->createBooking($trip, $passengers[0], 1, BookingStatus::Confirmed);
        $payment1 = $this->createPayment($booking1, $passengers[0], PaymentStatus::Completed, PaymentMethod::Card);
        $booking1->setPayment($payment1);
        $manager->persist($booking1);
        $manager->persist($payment1);

        // Réservation en attente de confirmation conducteur
        $booking2 = $this->createBooking($trip, $passengers[1], 1, BookingStatus::Pending);
        $payment2 = $this->createPayment($booking2, $passengers[1], PaymentStatus::Completed, PaymentMethod::Cash);
        $booking2->setPayment($payment2);
        $manager->persist($booking2);
        $manager->persist($payment2);
    }

    /**
     * @param User[] $passengers
     */
    private function createPastTripBookings(
        ObjectManager $manager,
        Trip $trip,
        array $passengers,
        User $driver,
    ): void {
        foreach (array_slice($passengers, 0, count(self::REVIEWS)) as $i => $passenger) {
            $booking = $this->createBooking($trip, $passenger, 1, BookingStatus::Confirmed);
            $payment = $this->createPayment($booking, $passenger, PaymentStatus::Completed, PaymentMethod::Card);
            $booking->setPayment($payment);
            $manager->persist($booking);
            $manager->persist($payment);

            [$rating, $comment] = self::REVIEWS[$i];

            $review = new Review();
            $review->setBooking($booking)
                ->setReviewer($passenger)
                ->setRating($rating)
                ->setComment($comment)
                ->setReviewed($driver);
            $manager->persist($review);
        }
    }

    private function createTrip(
        User $driver,
        Vehicle $vehicle,
        string $origin,
        string $dest,
        \DateTimeImmutable $departure,
        int $seats,
        float $price,
        TripStatus $status
    ): Trip {
        $trip = new Trip();
        $trip->setDriver($driver)
             ->setVehicle($vehicle)
             ->setOrigin($origin)
             ->setDestination($dest)
             ->setDepartureAt($departure)
             ->setAvailableSeats($seats)
             ->setPricePerSeat($price)
             ->setStatus($status);
        return $trip;
    }

    private function createBooking(
        Trip $trip,
        User $passenger,
        int $seats,
        BookingStatus $status
    ): Booking {
        $booking = new Booking();
        $booking->setTrip($trip)
                ->setPassenger($passenger)
                ->setSeatsBooked($seats)
                ->setStatus($status);
        return $booking;
    }

    private function createPayment(
        Booking $booking,
        User $payer,
        PaymentStatus $status,
        PaymentMethod $method
    ): Payment {
        $payment = new Payment();
        $payment->setBooking($booking)
                ->setPayer($payer)
                ->setAmount($booking->getTotalPrice())
                ->setStatus($status)
                ->setMethod($method)
                ->setStripeSessionId(sprintf('cs_test_fixture_%s_%d', uniqid(), rand(1000, 9999)));
        return $payment;
    }
}
