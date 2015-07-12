<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 * 
 * Copyright (C) 2015 Christoph Kappestein <k42b3.x@gmail.com>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Dependency;

use Fusio\App;
use Fusio\Authorization as ApiAuthorization;
use Fusio\Backend\Authorization as BackendAuthorization;
use Fusio\Connector;
use Fusio\Data\SchemaManager;
use Fusio\Factory;
use Fusio\Loader\DatabaseRoutes;
use Fusio\Loader\ResourceListing;
use Fusio\Loader\RoutingParser;
use Fusio\Logger;
use Fusio\Parser;
use Fusio\Processor;
use Fusio\Schema;
use Fusio\Template;
use Monolog\Logger as SystemLogger;
use PSX\Api;
use PSX\Data\Importer;
use PSX\Dependency\DefaultContainer;
use PSX\Log;
use PSX\Oauth2\Provider\GrantTypeFactory;

/**
 * Container
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Container extends DefaultContainer
{
    public function getApiGrantTypeFactory()
    {
        $factory = new GrantTypeFactory();
        $factory->add(new ApiAuthorization\ClientCredentials($this->get('connection')));

        return $factory;
    }

    public function getBackendGrantTypeFactory()
    {
        $factory = new GrantTypeFactory();
        $factory->add(new BackendAuthorization\ClientCredentials($this->get('connection')));

        return $factory;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        $logger = new SystemLogger('psx');
        //$logger->pushHandler(new Log\LogCasterHandler());

        return $logger;
    }

    /**
     * @return \PSX\Loader\RoutingParserInterface
     */
    public function getRoutingParser()
    {
        return new DatabaseRoutes($this->get('connection'));
    }

    /**
     * @return \PSX\Loader\LocationFinderInterface
     */
    public function getLoaderLocationFinder()
    {
        return new RoutingParser($this->get('connection'));
    }

    /**
     * @return \PSX\Data\Schema\SchemaManagerInterface
     */
    public function getApiSchemaManager()
    {
        return new SchemaManager($this->get('connection'));
    }

    /**
     * @return \PSX\Api\Resource\ListingInterface
     */
    public function getResourceListing()
    {
        $resourceListing = new ResourceListing($this->get('routing_parser'), $this->get('controller_factory'));

        if ($this->get('config')->get('psx_debug')) {
            return $resourceListing;
        } else {
            return new Api\Resource\Listing\CachedListing($resourceListing, $this->get('cache'));
        }
    }

    /**
     * @return \Fusio\Logger
     */
    public function getApiLogger()
    {
        return new Logger($this->get('connection'));
    }

    /**
     * @return \Fusio\Parser\Action
     */
    public function getActionParser()
    {
        return new Parser\Action(
            $this->get('action_factory'),
            $this->get('config')->get('fusio_action'),
            'Fusio\ActionInterface'
        );
    }

    /**
     * @return \Fusio\Factory\Action
     */
    public function getActionFactory()
    {
        return new Factory\Action($this->get('object_builder'));
    }

    /**
     * @return \Fusio\Processor
     */
    public function getProcessor()
    {
        return new Processor($this->get('connection'), $this->get('action_factory'));
    }

    /**
     * @return \Fusio\Parser\Connection
     */
    public function getConnectionParser()
    {
        return new Parser\Connection(
            $this->get('connection_factory'),
            $this->get('config')->get('fusio_connection'),
            'Fusio\ConnectionInterface'
        );
    }

    /**
     * @return \Fusio\Factory\Connection
     */
    public function getConnectionFactory()
    {
        return new Factory\Connection($this->get('object_builder'));
    }

    /**
     * @return \Fusio\Connector
     */
    public function getConnector()
    {
        return new Connector($this->get('connection'), $this->get('connection_factory'));
    }

    /**
     * @return \Fusio\Schema\Parser
     */
    public function getSchemaParser()
    {
        return new Schema\Parser($this->get('connection'));
    }

    /**
     * @return \Fusio\Schema\Loader
     */
    public function getSchemaLoader()
    {
        return new Schema\Loader($this->get('connection'));
    }

    /**
     * @return \Fusio\App\Loader
     */
    public function getAppLoader()
    {
        return new App\Loader($this->get('connection'));
    }

    /**
     * @return \Fusio\Template\Parser
     */
    public function getTemplateParser()
    {
        return new Template\Parser($this->get('config')->get('psx_debug'));
    }
}