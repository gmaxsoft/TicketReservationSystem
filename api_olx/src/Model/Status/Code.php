<?php

namespace ServiceAdvert\Model\Status;

class Code
{
    /*
     * Local code type
     */
    const NOT_INIT = 'not_init';

    /*
     * Ad code types at the API level
     */
    const ACTIVE = 'active';

    const UNPAID = 'unpaid';

    const EMPTY_CODE = 'empty code';

    const OUTDATED_BY_PACKAGE = 'outdated_by_package';

    const MODERATED = 'moderated';

    const OUTDATED = 'outdated';

    const REMOVED_BY_MODERATOR = 'removed_by_moderator';

    const REMOVED_BY_USER = 'removed_by_user';

    const REMOVED_BY_PARENT_AD = 'removed_by_parent_ad';
}
