<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Service;

use EMS\CommonBundle\Helper\Text\Encoder;
use EMS\CoreBundle\Entity\Channel;
use EMS\CoreBundle\Repository\ChannelRepository;
use Psr\Log\LoggerInterface;

final class ChannelService implements EntityServiceInterface
{
    /** @var ChannelRepository */
    private $channelRepository;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(ChannelRepository $channelRepository, LoggerInterface $logger)
    {
        $this->channelRepository = $channelRepository;
        $this->logger = $logger;
    }

    /**
     * @return Channel[]
     */
    public function getAll(): array
    {
        return $this->channelRepository->getAll();
    }

    public function update(Channel $channel): void
    {
        if (0 === $channel->getOrderKey()) {
            $channel->setOrderKey($this->channelRepository->counter() + 1);
        }
        $encoder = new Encoder();
        $name = $channel->getName();
        if (null === $name) {
            throw new \RuntimeException('Unexpected null name');
        }
        $webalized = $encoder->webalize($name);
        if (null === $webalized) {
            throw new \RuntimeException('Unexpected null webalized name');
        }
        $channel->setName($webalized);
        $this->channelRepository->create($channel);
    }

    public function delete(Channel $channel): void
    {
        $name = $channel->getName();
        $this->channelRepository->delete($channel);
        $this->logger->warning('log.service.channel.delete', [
            'name' => $name,
        ]);
    }

    /**
     * @param string[] $ids
     */
    public function deleteByIds(array $ids): void
    {
        foreach ($this->channelRepository->getByIds($ids) as $channel) {
            $this->delete($channel);
        }
    }

    /**
     * @param array<string, int> $ids
     */
    public function reorderByIds(array $ids): void
    {
        foreach ($this->channelRepository->getByIds(\array_keys($ids)) as $channel) {
            $channel->setOrderKey($ids[$channel->getId()] ?? 0);
            $this->channelRepository->create($channel);
        }
    }

    public function isSortable(): bool
    {
        return true;
    }

    /**
     * @param mixed $context
     *
     * @return Channel[]
     */
    public function get(int $from, int $size, $context = null): array
    {
        if (null !== $context) {
            throw new \RuntimeException('Unexpected context');
        }

        return $this->channelRepository->get($from, $size);
    }

    public function getEntityName(): string
    {
        return 'channel';
    }

    /**
     * @param mixed $context
     */
    public function count($context = null): int
    {
        if (null !== $context) {
            throw new \RuntimeException('Unexpected non-null object');
        }

        return $this->channelRepository->counter();
    }
}
