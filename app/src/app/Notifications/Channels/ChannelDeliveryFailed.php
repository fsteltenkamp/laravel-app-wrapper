<?php

namespace App\Notifications\Channels;

use RuntimeException;

/**
 * The message is shown to the channel's owner, so it must not contain the
 * remote service's response or anything else learned from the target.
 */
class ChannelDeliveryFailed extends RuntimeException {}
