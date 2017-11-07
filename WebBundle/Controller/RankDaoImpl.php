<?php
namespace Topxia\Service\Rank\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Rank\Dao\RankDao;

class RankDaoImpl extends BaseDao implements RankDao
{
    protected $table = 'rank_school';

    // private $serializeFields = array(
    //     'answer' => 'json',
    //     'metas'  => 'json'
    // );

    public function getRank($id)
    {
        $sql  = "SELECT * FROM `rank_school` AS rs LEFT JOIN `school` AS s ON rs.school_id = s.id
                        LEFT JOIN `rank_category` AS rc ON rs.cate_id = rc.id ;";
        $Rank = $this->getConnection()->fetchAssoc($sql);
        return $Rank;
        // return $Rank ? $this->createSerializer()->unserialize($Rank, $this->serializeFields) : null;
    }

    public function getRankCategories() 
    {
        $sql = "SELECT id,name FROM `rank_category` WHERE valid = '1'";
        $cates = $this->getConnection()->fetchAll($sql);
        // echo '<pre>';print_r( $cates );exit;
        // return $cates ? $this->createSerializer()->unserializes($cates, array('name'=>'json')) : null;
        return $cates;
    }

    public function getSchoolRanksByCateId($id) 
    {
        //id 为分类id  默认为1
        $sql = "SELECT s.id s_id,s.website url,s.name_cn c_name,s.logo_sqr pic FROM `school`
                             AS s LEFT JOIN `rank_school` AS rs on s.id = rs.school_id 
                             WHERE rs.cate_id = {$id} 
                             ORDER BY rs.rank";
        $ranks = $this->getConnection()->fetchAll($sql);
        // echo '<pre>';print_r( $ranks );exit;
        return $ranks ? $this->createSerializer()->unserializes($ranks, array('s_name'=>'json')) : null;
    }

    public function findRanksByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks     = str_repeat('?,', count($ids) - 1).'?';
        $sql       = "SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        $Ranks = $this->getConnection()->fetchAll($sql, $ids);
        return $this->createSerializer()->unserializes($Ranks, $this->serializeFields);
    }

    public function findRanksByCopyIds(array $copyIds)
    {
        if (empty($copyIds)) {
            return array();
        }

        $marks     = str_repeat('?,', count($copyIds) - 1).'?';
        $sql       = "SELECT * FROM {$this->table} WHERE copyId IN ({$marks});";
        $Ranks = $this->getConnection()->fetchAll($sql, $copyIds);
        return $this->createSerializer()->unserializes($Ranks, $this->serializeFields);
    }

    public function findRanksByParentId($id)
    {
        $sql       = "SELECT * FROM {$this->table} WHERE parentId = ? ORDER BY createdTime ASC";
        $Ranks = $this->getConnection()->fetchAll($sql, array($id));
        return $this->createSerializer()->unserializes($Ranks, $this->serializeFields);
    }

    public function findRanksByCopyIdAndLockedTarget($copyId, array $lockedTargets)
    {
        if(empty($lockedTargets)) {
            return array();
        }

        $marks     = str_repeat('?,', count($lockedTargets) - 1).'?';

        $sql = "SELECT * FROM {$this->table} WHERE copyId = ? AND target IN ({$marks})";
        return $this->getConnection()->fetchAll($sql, array_merge(array($copyId), $lockedTargets));
    }

    //@todo:sql 未用到
    public function findRanksbyTypes(array $types, $start, $limit)
    {
        if (empty($types)) {
            return array();
        }

        $this->filterStartLimit($start, $limit);

        $marks     = str_repeat('?,', count($types) - 1).'?';

        $sql       = "SELECT * FROM {$this->table} WHERE `parentId` = 0 AND type in ({$marks})  LIMIT {$start},{$limit}";
        $Ranks = $this->getConnection()->fetchAll($sql, $types);
        return $this->createSerializer()->unserializes($Ranks, $this->serializeFields);
    }

    //@todo:sql 未用到
    public function findRanksByTypesAndExcludeUnvalidatedMaterial(array $types, $start, $limit)
    {
        if (empty($types)) {
            return array();
        }

        $this->filterStartLimit($start, $limit);
        $marks     = str_repeat('?,', count($types) - 1).'?';

        $sql       = "SELECT * FROM {$this->table} WHERE (`parentId` = 0) AND (`type` in ({$marks})) and ( not( `type` = 'material' and `subCount` = 0 )) LIMIT {$start},{$limit} ";
        $Ranks = $this->getConnection()->fetchAll($sql, $types);
        return $this->createSerializer()->unserializes($Ranks, $this->serializeFields);
    }

    //todo: fix
    public function findRanksByTypesAndSourceAndExcludeUnvalidatedMaterial($types, $start, $limit, $RankSource, $courseId, $lessonId)
    {
        if (empty($types)) {
            return array();
        }

        if ($RankSource == 'course') {
            $target = 'course-'.$courseId;
        } elseif ($RankSource == 'lesson') {
            $target = 'course-'.$courseId.'/lesson-'.$lessonId;
        }

        $this->filterStartLimit($start, $limit);

        $sql = "SELECT * FROM {$this->table} WHERE (`parentId` = 0) and  (`type` in ($types)) and ( not( `type` = 'material' and `subCount` = 0 )) and (`target` like ? OR `target` = ?) LIMIT {$start},{$limit} ";

        $Ranks = $this->getConnection()->fetchAll($sql, array("{$target}/%", "{$target}"));
        return $this->createSerializer()->unserializes($Ranks, $this->serializeFields);
    }

    //@todo:sql 未用到
    public function findRanksCountbyTypes(array $types)
    {
        if (empty($types)) {
            return 0;
        }

        $marks     = str_repeat('?,', count($types) - 1).'?';

        $sql = "SELECT count(*) FROM {$this->table} WHERE type in ({$marks})";
        return $this->getConnection()->fetchColumn($sql, $types);
    }


    //todo: fix
    public function findRanksCountbyTypesAndSource($types, $RankSource, $courseId, $lessonId)
    {
        if ($RankSource == 'course') {
            $target = 'course-'.$courseId;
        } elseif ($RankSource == 'lesson') {
            $target = 'course-'.$courseId.'/lesson-'.$lessonId;
        }

        $sql = "SELECT count(*) FROM {$this->table} WHERE  (`parentId` = 0) and (`type` in ({$types})) and (`target` like ? OR `target` = ?)";
        return $this->getConnection()->fetchColumn($sql, array("{$target}/%", "{$target}"));
    }

    public function findRanksByParentIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks     = str_repeat('?,', count($ids) - 1).'?';
        $sql       = "SELECT * FROM {$this->table} WHERE parentId IN ({$marks});";
        $Ranks = $this->getConnection()->fetchAll($sql, $ids);
        return $this->createSerializer()->unserializes($Ranks, $this->serializeFields);
    }

    public function searchRanks($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $this->checkOrderBy($orderBy, array('createdTime'));

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->orderBy($orderBy[0], $orderBy[1]);
        $Ranks = $builder->execute()->fetchAll() ?: array();
        
        return $this->createSerializer()->unserializes($Ranks, $this->serializeFields);
    }

    public function searchRanksCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function findRanksCountByParentId($parentId)
    {
        $sql = "SELECT count(*) FROM {$this->table} WHERE parentId = ?";
        return $this->getConnection()->fetchColumn($sql, array($parentId));
    }

    public function findRanksByTarget($target)
    {
        $sql = "SELECT * FROM {$this->table} WHERE target = ?";
        $Ranks = $this->getConnection()->fetchAll($sql, array($target));
        return $this->createSerializer()->unserializes($Ranks, $this->serializeFields);
    }

    public function addRank($fields)
    {
        $fields   = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert Rank error.');
        }

        return $this->getRank($this->getConnection()->lastInsertId());
    }

    public function updateRank($id, $fields)
    {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getRank($id);
    }

    public function deleteRank($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteRanksByParentId($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE parentId = ?";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }

    public function updateRankCountByIds($ids, $status)
    {
        if (empty($ids)) {
            return array();
        }

        $fields = array('finishedTimes', 'passedTimes');

        if (!in_array($status, $fields)) {
            throw \InvalidArgumentException(sprintf($this->getKernel()->trans("%status%字段不允许增减，只有%fields%才被允许增减",array('%status%'=>$status,'%fields%'=>implode(',', $fields)))));
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "UPDATE {$this->table} SET {$status} = {$status}+1 WHERE id IN ({$marks})";
        return $this->getConnection()->executeQuery($sql, $ids);
    }

    //@todo:sql
    public function getRankCountGroupByTypes($conditions)
    {
        $sqlConditions = array();
        $sql           = "";

        if (isset($conditions["types"])) {
            $marks = str_repeat('?,', count($conditions["types"]) - 1).'?';
            $sql .= " and type IN ({$marks}) ";
            $sqlConditions = array_merge($sqlConditions, $conditions["types"]);
        }

        if (isset($conditions["targets"])) {
            $targetMarks   = str_repeat('?,', count($conditions["targets"]) - 1).'?';
            $sqlConditions = array_merge($sqlConditions, $conditions["targets"]);
            $sql .= " and target IN ({$targetMarks}) ";
        }

        if (isset($conditions["courseId"])) {
            $sql .= " and (target=? OR target like ?) ";
            $sqlConditions[] = "course-{$conditions['courseId']}";
            $sqlConditions[] = "course-{$conditions['courseId']}/%";
        }

        $sql = "SELECT COUNT(*) AS RankNum, type FROM {$this->table} WHERE parentId = '0' {$sql} GROUP BY type ";
        return $this->getConnection()->fetchAll($sql, $sqlConditions);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if ($value === '' || is_null($value)) {
                return false;
            }

            return true;
        }

        );

        if (isset($conditions['targetPrefix'])) {
            $conditions['targetLike'] = "{$conditions['targetPrefix']}/%";
            unset($conditions['target']);
        }

        if (isset($conditions['stem'])) {
            $conditions['stem'] = "%{$conditions['stem']}%";
        }

        if (isset($conditions['targets']) && is_array($conditions['targets'])) {
            unset($conditions['target']);
            unset($conditions['targetPrefix']);
        }

        if (isset($conditions['type']) && $conditions['type'] == '0') {
            unset($conditions['type']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'Ranks')
            ->andWhere("target IN ( :targets )")
            ->andWhere('target = :target')
            ->andWhere('target = :targetPrefix OR target LIKE :targetLike')
            ->andWhere('parentId = :parentId')
            ->andWhere('difficulty = :difficulty')
            ->andWhere('type = :type')
            ->andWhere('stem LIKE :stem')
            ->andWhere("type IN ( :types )")
            ->andwhere("subCount <> :subCount")
            ->andWhere("id NOT IN ( :excludeIds ) ")
            ->andWhere('copyId = :copyId');

        if (isset($conditions['excludeUnvalidatedMaterial']) && ($conditions['excludeUnvalidatedMaterial'] == 1)) {
            $builder->andStaticWhere(" not( type = 'material' AND subCount = 0 )");
        }

        return $builder;
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
