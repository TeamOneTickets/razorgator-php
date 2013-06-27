<?php

/**
 * Razorgator PHP Client Library
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://github.com/teamonetickets/razorgator-php/blob/master/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@teamonetickets.com so we can send you a copy immediately.
 *
 * @category    Razorgator
 * @package     Razorgator\Client
 * @subpackage  ResultSet
 * @author      J Cobb <j@teamonetickets.com>
 * @copyright   Copyright (c) 2013 Team One Tickets & Sports Tours, Inc. (http://www.teamonetickets.com)
 * @license     https://github.com/teamonetickets/razorgator-php/blob/master/LICENSE.txt    BSD 3-Clause License
 */


namespace Razorgator\Client\ResultSet;


/**
 * @category    Razorgator
 * @package     Razorgator\Client
 * @subpackage  ResultSet
 * @copyright   Copyright (c) 2013 Team One Tickets & Sports Tours, Inc. (http://www.teamonetickets.com)
 * @license     https://github.com/teamonetickets/razorgator-php/blob/master/LICENSE.txt    BSD 3-Clause License
 */
class AbstractResultSet
    implements \SeekableIterator, \Countable
{
    /**
     * An array of objects
     *
     * @var array
     */
    protected $_results = null;

    /**
     * Current index for SeekableIterator
     *
     * @var int
     */
    protected $_currentIndex = 0;


    /**
     * Create the ResultSet
     *
     * @param  object $result
     * @return void
     */
    public function __construct($result)
    {
        $xmlIterator = new \SimpleXMLIterator($result);
        for ($xmlIterator->rewind(); $xmlIterator->valid(); $xmlIterator->next()) {
            $temp = new \stdClass();
            foreach ($xmlIterator->getChildren()->attributes() as $name => $value) {
                switch ($name) {
                    case 'orderId':
                    case 'brokerTicketId':
                    case 'quantity':
                    case 'purchaseOrderId':
                        $temp->$name = (int) $value;
                        break;

                    case 'cost':
                        $temp->$name = (float) $value;
                        break;

                    case 'electronicDelivery':
                        $temp->$name = (bool) $value;
                        break;

                    default:
                        $temp->$name = (string) $value;
                }
            }

            $this->_results[] =  $temp;
        }

        unset($xmlIterator, $temp, $name, $value);

    }


    /**
     * Number of results returned in this ResultSet
     *
     * @return int Total number of results returned
     */
    public function count()
    {
        return (int) count($this->_results);
    }


    /**
     * Total Number of results available
     *
     * @return int Total number of results available
     */
    public function totalResults()
    {
        return $this->count();
    }


    /**
     * Implement SeekableIterator::current()
     *
     * @return mixed
     */
    public function current()
    {
        return $this->_results[$this->_currentIndex];
    }


    /**
     * Implement SeekableIterator::key()
     *
     * @return int
     */
    public function key()
    {
        return $this->_currentIndex;
    }


    /**
     * Implement SeekableIterator::next()
     *
     * @return void
     */
    public function next()
    {
        $this->_currentIndex += 1;
    }


    /**
     * Implement SeekableIterator::rewind()
     *
     * @return void
     */
    public function rewind()
    {
        $this->_currentIndex = 0;
    }


    /**
     * Implement SeekableIterator::seek()
     *
     * @param  int $index
     * @throws \OutOfBoundsException
     * @return void
     */
    public function seek($index)
    {
        $indexInt = (int) $index;
        if ($indexInt >= 0 && (null === $this->_results || $indexInt < count($this->_results))) {
            $this->_currentIndex = $indexInt;
        } else {
            throw new \OutOfBoundsException("Illegal index '$index'");
        }
    }


    /**
     * Implement SeekableIterator::valid()
     *
     * @return boolean
     */
    public function valid()
    {
        return null !== $this->_results && $this->_currentIndex < count($this->_results);
    }


    /**
     * Remove entries that are in the supplied array
     * This is mainly used after performing a listTicketGroups() and can be used
     * to pass in an array of brokerage IDs to filter out their inventory if
     * you do not want it to show.
     *
     * Usage: $results = $tevo->listTicketGroups($options);
     *        $excludeArray = array(1,3,5,7,9);
     *        $results->excludeResults($excludeArray, 'brokerage');
     *
     * @param array $exclude   An array of brokerage IDs to REMOVE
     * @return void
     */
    public function excludeResults(array $exclude, $type='brokerage')
    {
        if ($type == 'brokerage') {
            // In ticketGroups brokerage is now a nested property of office
            $this->_results = array_filter(
                $this->_results, function($v) use($exclude, $type) {
                    return !in_array($v->office->$type->id, $exclude);
                }
            );
        } elseif ($type == 'office') {
            $this->_results = array_filter(
                $this->_results, function($v) use($exclude, $type) {
                    return !in_array($v->office->id, $exclude);
                }
            );
        } else {
            $this->_results = array_filter(
                $this->_results, function($v) use($exclude, $type) {
                    return !in_array($v->$type, $exclude);
                }
            );
        }

        // Put the keys back in order, filling in any now-missing keys
        sort($this->_results);
    }


    /**
     * Returns the entire $_results array as an array.
     *
     * Tests show that when looping through all the results, such as when
     * displaying all the TicketGroups on your website you can actually loop
     * over the array returned by this faster than you can loop over the entire
     * object.
     *
     * In one test looping through 400 items went from .03 seconds down to .013.
     *
     * If you want the ultimate in over-optimization you can use this. Make sure
     * you use sortResults(), excludeResults() or exclusiveResults() first, as
     * they obviously will not be available in the array returned by this method.
     *
     * @return array
     */
    public function getResultsAsArray() {
        return $this->_results;
    }

}
