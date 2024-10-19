<?php

namespace App;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTags
{
    public function tags(): Collection
    {
        return $this->tagsRelation;
    }

    public function syncTags(\Illuminate\Support\Collection|array $tags)
    {
        $this->save();
        $this->tagsRelation()->sync($tags);

        $this->unsetRelation('tagsRelation');
    }

    public function removeTags()
    {
        $this->tagsRelation()->detach();

        $this->unsetRelation('tagsRelation');
    }

    public function tagsRelation(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->withTimestamps();
    }
}
