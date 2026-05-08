<?php

namespace App\DataFixtures;

use App\Entity\Booking;
use App\Entity\Payment;
use App\Entity\Review;
use App\Entity\Trip;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Enum\BookingStatus;
use App\Enum\PaymentStatus;
use App\Enum\TripStatus;
use App\Enum\UserStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const CITIES = [
        'Paris', 'Lyon', 'Marseille', 'Bordeaux', 'Toulouse', 'Nantes', 'Strasbourg', 'Lille', 'Rennes', 'Nice',
        'Angers', 'Rouen'
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

        /*
        $trips = [];
        foreach ($users as $driverIndex => $driver) {
            $driverVehicles = $vehicles[$driverIndex];
            $vehicle = $driverVehicles[0];
            $tripFull = $this->createTrip(
                driver: $driver,
                vehicle: $vehicle,
                origin: self::CITIES[$driverIndex],
                dest: self::CITIES[($driverIndex + 1) % count(self::CITIES)],
                departure: new \DateTimeImmutable(sprintf('+%d days', $driverIndex + 3)),
                seats: 2,
                price: 10.0 + $driverIndex,
                status: TripStatus::Full
            );
            $manager->persist($tripFull);
            $trips[] = ['trip' => $tripFull, 'driver' => $driver, 'type' => 'full'];

            $tripOpen = $this->createTrip(
                driver: $driver,
                vehicle: $vehicle,
                origin: self::CITIES[($driverIndex + 2) % count(self::CITIES)],
                dest: self::CITIES[($driverIndex + 3) % count(self::CITIES)],
                departure: new \DateTimeImmutable(sprintf('+%d days', $driverIndex + 10)),
                seats: 4,
                price: 15.0 + $driverIndex,
                status: TripStatus::Open
            );
            $manager->persist($tripOpen);
            $trips[] = ['trip' => $tripOpen, 'driver' => $driver, 'type' => 'open'];

            $tripPast = $this->createTrip(
                driver: $driver,
                vehicle: $vehicle,
                origin: self::CITIES[($driverIndex + 4) % count(self::CITIES)],
                dest: self::CITIES[($driverIndex + 5) % count(self::CITIES)],
                departure: new \DateTimeImmutable(sprintf('-%d days', $driverIndex + 5)),
                seats: 3,
                price: 12.0 + $driverIndex,
                status: TripStatus::Completed
            );
            $manager->persist($tripPast);
            $trips[] = ['trip' => $tripPast, 'driver' => $driver, 'type' => 'past'];
        }
        $manager->flush();
        */
        $trips = [];

        /**
         * 3 trajets passés
         */
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

        /**
         * 9 trajets futurs NON complets
         */
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
        $payment1 = $this->createPayment($booking1, $passengers[0], PaymentStatus::Completed);
        $booking1->setPayment($payment1);
        $manager->persist($booking1);
        $manager->persist($payment1);

        // Réservation en attente de confirmation conducteur
        $booking2 = $this->createBooking($trip, $passengers[1], 1, BookingStatus::Pending);
        $payment2 = $this->createPayment($booking2, $passengers[1], PaymentStatus::Completed);
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
            $payment = $this->createPayment($booking, $passenger, PaymentStatus::Completed);
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
        PaymentStatus $status
    ): Payment {
        $payment = new Payment();
        $payment->setBooking($booking)
                ->setPayer($payer)
                ->setAmount($booking->getTotalPrice())
                ->setStatus($status)
                ->setStripeSessionId(sprintf('cs_test_fixture_%s_%d', uniqid(), rand(1000, 9999)));
        return $payment;
    }
}
