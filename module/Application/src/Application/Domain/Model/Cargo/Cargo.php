<?php
/*
 * This file is part of the codeliner/php-ddd-cargo-sample package.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Application\Domain\Model\Cargo;

use Application\Domain\Shared\EntityInterface;
use Rhumsaa\Uuid\Uuid;

/**
 * A Cargo. This is the central class in the domain model.
 *
 * A cargo is identified by a unique tracking id, and it always has an origin
 * and a route specification. The life cycle of a cargo begins with the booking procedure,
 * when the tracking id is assigned. During a (short) period of time, between booking
 * and initial routing, the cargo has no itinerary.
 *
 * The booking clerk requests a list of possible routes, matching the route specification,
 * and assigns the cargo to one route. The route to which a cargo is assigned is described
 * by an itinerary.
 *
 * A cargo can be re-routed during transport, on demand of the customer, in which case
 * a new route is specified for the cargo and a new route is requested. The old itinerary,
 * being a value object, is discarded and a new one is attached.
 * 
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class Cargo implements EntityInterface
{
    /**
     * Unique Identifier
     * 
     * @var string
     */
    private $trackingIdString;
    
    /**
     * @var string
     */
    private $origin;
    
    /**
     *
     * @var RouteSpecification 
     */
    private $routeSpecification;

    /**
     * @var Itinerary
     */
    private $itinerary;

    /**
     * Construct
     *
     * @param TrackingId $aTrackingId
     * @param RouteSpecification $aRouteSpecification
     */
    public function __construct(TrackingId $aTrackingId, RouteSpecification $aRouteSpecification)
    {
        //Unfortunately, doctrine does not work with ValueObjects as identifier,
        //so we have to use the string representation internally
        //@see http://www.doctrine-project.org/jira/browse/DDC-2984
        $this->trackingIdString = $aTrackingId->toString();

        //Construct is only called when the Cargo is initially created.
        //Doctrine do not call __construct when it recreates a persisted entity.
        //Therefor we can assign the origin here.
        //It will be always the same for that specific Cargo even if the RouteSpecification changes.
        $this->origin     = $aRouteSpecification->origin();

        $this->routeSpecification = $aRouteSpecification;
    }
    
    /**
     * @return TrackingId Unique Identifier of this Cargo
     */
    public function trackingId()
    {
        return new TrackingId(Uuid::fromString($this->trackingIdString));
    }
    
    /**
     * @return string Origin of this Cargo
     */
    public function origin()
    {
        return $this->origin;
    }

    /**
     * @return RouteSpecification
     */
    public function routeSpecification()
    {
        return $this->routeSpecification;
    }

    /**
     * Specifies a new route for this cargo.
     *
     * @param RouteSpecification $aRouteSpecification
     */
    public function specifyNewRoute(RouteSpecification $aRouteSpecification)
    {
        $this->routeSpecification = $aRouteSpecification;
    }

    /**
     * @return Itinerary Never null
     */
    public function itinerary()
    {
        if (is_null($this->itinerary)) {
            return new Itinerary(array());
        } else {
            return $this->itinerary;
        }
    }

    /**
     * Attach a new itinerary to this cargo.
     *
     * @param Itinerary $anItinerary
     */
    public function assignToRoute(Itinerary $anItinerary)
    {
        $this->itinerary = $anItinerary;
    }
        
    /**
     * {@inheritDoc}
     */
    public function sameIdentityAs(EntityInterface $other)
    {
        if (!$other instanceof Cargo) {
            return false;
        }
        
        return $this->trackingId()->sameValueAs($other->trackingId());
    }
}
