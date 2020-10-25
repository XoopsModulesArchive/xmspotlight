<?php
/**
 *  this is for news 1.5x
 **/
if (!defined('XOOPS_ROOT_PATH')) {
    die('XOOPS root path not defined');
}
require_once XOOPS_ROOT_PATH . '/modules/news/class/class.newsstory.php';

/**
 * Extends NewsStory (a class in the News Module)
 * Returns published stories according to some options
 **/
class XMspotlightStory extends NewsStory
{
    /**
     * Returns published stories according to some options
     * @param mixed $limit
     * @param mixed $start
     * @param mixed $checkRight
     * @param mixed $topic
     * @param mixed $ihome
     * @param mixed $asobject
     * @param mixed $order
     * @param mixed $topic_frontpage
     * @param mixed $subs
     *
     * @return array|null
     * @return array|null
     */

    public function getAllPublishedMore($limit = 0, $start = 0, $checkRight = false, $topic = 0, $ihome = 0, $asobject = true, $order = 'published', $topic_frontpage = false, $subs = false)
    {
        //global $xoopsDB;

        $db = XoopsDatabaseFactory::getDatabaseConnection();

        $myts = MyTextSanitizer::getInstance();

        $ret = [];

        $critadd = '';

        //if subs is true

        if (true === $subs) {
            $sqlz = 'SELECT topic_id FROM ' . $db->prefix('topics') . ' WHERE (topic_pid=' . $topic . ')';

            $resultz = $db->query($sqlz);

            while ($topicz = $db->fetchArray($resultz)) {
                $critadd .= ' OR topicid=' . $topicz['topic_id'] . ' ';
            }

            //$critadd .= ")";
        }

        $sql = 'SELECT s.*, t.* FROM ' . $db->prefix('stories') . ' s, ' . $db->prefix('topics') . ' t WHERE (published > 0 AND published <= ' . time() . ') AND (expired = 0 OR expired > ' . time() . ') AND (s.topicid=t.topic_id) ';

        if (0 != $topic) {
            if (!is_array($topic)) {
                if ($checkRight) {
                    $topics = news_MygetItemIds('news_view');

                    if (!in_array($topic, $topics, true)) {
                        return null;
                    }  

                    $sql .= ' AND (topicid=' . (int)$topic . ' ' . $critadd . ') AND (ihome=1 OR ihome=0)';
                } else {
                    $sql .= ' AND topicid=' . (int)$topic . ' AND (ihome=1 OR ihome=0)';
                }
            } else {
                if ($checkRight) {
                    $topics = news_MygetItemIds('news_view');

                    $topic = array_intersect($topic, $topics);
                }

                if (count($topic) > 0) {
                    $sql .= ' AND topicid IN (' . implode(',', $topic) . ')';
                } else {
                    return null;
                }
            }
        } else {
            if ($checkRight) {
                $topics = news_MygetItemIds('news_view');

                if (count($topics) > 0) {
                    $topics = implode(',', $topics);

                    $sql .= ' AND topicid IN (' . $topics . ')';
                } else {
                    return null;
                }
            }

            if (0 == (int)$ihome) {
                $sql .= ' AND ihome=0';
            }
        }

        if ($topic_frontpage) {
            $sql .= ' AND t.topic_frontpage=1';
        }

        $sql .= " ORDER BY s.$order DESC";

        $result = $db->query($sql, (int)$limit, (int)$start);

        while ($myrow = $db->fetchArray($result)) {
            if ($asobject) {
                $ret[] = new NewsStory($myrow);
            } else {
                $ret[$myrow['storyid']] = htmlspecialchars($myrow['title'], ENT_QUOTES | ENT_HTML5);
            }
        }

        return $ret;
    }
}
