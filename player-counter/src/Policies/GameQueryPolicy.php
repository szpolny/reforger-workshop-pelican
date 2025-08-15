<?php

namespace Boy132\PlayerCounter\Policies;

use App\Policies\DefaultPolicies;

class GameQueryPolicy
{
    use DefaultPolicies;

    protected string $modelName = 'game_query';
}
