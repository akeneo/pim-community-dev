<?php

namespace Pim\Bundle\AnalyticsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use TweedeGolf\PrometheusClient\CollectorRegistry;
use TweedeGolf\PrometheusClient\Format\TextFormatter;

/**
 * Prometheus controller
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PrometheusController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        $registry = $this->get(CollectorRegistry::class);
        $formatter = new TextFormatter();

        $registry->getGauge('pim_channels_total')->set($this->get('pim_api.repository.channel')->count());
        $registry->getGauge('pim_products_total')->set($this->get('pim_catalog.repository.product')->countAll());
        $registry->getGauge('pim_families_total')->set($this->get('pim_api.repository.family')->count());

        $registry->getGauge('pim_locales_total')->set(
            $this->get('pim_api.repository.locale')->count(['activated' => true]),
            ['status' => 'active']
        );

        $registry->getGauge('pim_locales_total')->set(
            $this->get('pim_api.repository.locale')->count(['activated' => false]),
            ['status' => 'inactive']
        );

        $registry->getGauge('pim_users_total')->set(
            $this->get('pim_user.repository.user')->countBy(['enabled' => true]),
            ['status' => 'enabled']
        );

        $registry->getGauge('pim_users_total')->set(
            $this->get('pim_user.repository.user')->countBy(['enabled' => false]),
            ['status' => 'disabled']
        );

        return new Response($formatter->format($registry->collect()), 200, [
            'Content-Type' => $formatter->getMimeType(),
        ]);
    }
}
