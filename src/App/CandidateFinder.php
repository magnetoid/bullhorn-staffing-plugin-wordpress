<?php

namespace WPBullhornStaffing\App;

use WPBullhornStaffing\Domain\Entities\CandidateFiles;
use WPBullhornStaffing\Domain\Entities\CandidateInfo;
use WPBullhornStaffing\Domain\Entities\WPCandidate;

class CandidateFinder
{

    /**
     * @param \WP_User $user
     * @return WPCandidate|null
     */
    public function find(\WP_User $user): ?WPCandidate
    {
        $bhId = apply_filters('wpbhs_get_bh_id', $user);
        if (!is_numeric($bhId)) {
            $bhId = get_user_meta($user, 'bh_id', true);
        }

        if ($bhId) {
            return (new WPCandidate(
                $user,
                $bhId,
                [
                    'candidateInfoClass' => apply_filters('wpbhs_get_candidate_info_class', CandidateInfo::class, $user),
                    'candidateFilesClass' => apply_filters('wpbhs_get_candidate_files_class', CandidateFiles::class, $user),
                ]
            ));
        }
        return null;
    }

}