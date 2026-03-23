<?php

namespace ServiceAdvert\Model\Status;

class Event
{
    /*
     * PUBLISH_ADVERT
     * Events types related to the publish_advert event flow
     */
    const ADVERT_POSTED_SUCCESS = 'advert_posted_success';

    const ADVERT_POSTED_ERROR = 'advert_posted_error';

    const ADVERT_PUT_SUCCESS = 'advert_put_success';

    const ADVERT_PUT_ERROR = 'advert_put_error';

    const ADVERT_DELETED_SUCCESS = 'advert_deleted_success';

    const ADVERT_DELETED_ERROR = 'advert_deleted_error';

    const ADVERT_DEACTIVATED_SUCCESS = 'advert_deactivated_success';

    const ADVERT_DEACTIVATED_ERROR = 'advert_deactivated_error';

    const ADVERT_ACTIVATED_SUCCESS = 'advert_activated_success';

    const ADVERT_ACTIVATED_ERROR = 'advert_activated_error';

    const ADVERT_IMAGE_ERROR = 'advert_image_error';

    /*
     * ADVERT_LIFECYCLE
     * Events types related to the advert_lifecycle event flow
     */
    const ADVERT_STATE_CHANGED = 'advert_state_changed';

    /*
     * INCOMING_MESSAGE
     * Events types related to the incoming_message event flow
     */
    const INCOMING_MESSAGE = 'incoming_message_success';

    /*
     * VAS
     * Events types related to the vas event flow
     */
    const ADVERT_VAS_APPLIED = 'advert_vas_applied';

    const ADVERT_VAS_EXPIRED = 'advert_vas_expired';

    const VAS_PROMOTION_ERROR = 'vas_promotion_error';
}
