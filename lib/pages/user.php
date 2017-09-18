<?php
/****************
Users page:
 * Activtaion
 *
 ****************/


switch ($_GET['page2']){
    case 'activation':{
        $_tmp = $f2backend -> mod_activateUser($_GET['page3'],$_GET['page4']);
        if($_tmp['status'] == TRUE) {
            $body = '
                <div class="row">
                    <div class="col-4">
                     <br>
                    </div>
                    <div class="col-4">
                        <div class="message succes"><h3>Account activated!</h3></div>
                    </div>
                    <div class="col-4">
                     <br>
                    </div>
                </div>
                ';
            $body .= '<script>setTimeout(function () { window.location.replace("http://forty2reviews.com"); }, 800);</script>';
        }else{
            $body = '
                <div class="row">
                    <div class="col-4">
                    <br>
                    </div>
                    <div class="col-4">
                        <div class="message error"><h3>' . $_tmp['message'] . '</h3></div>
                    </div>
                    <div class="col-4">
                    <br>
                    </div>
                </div>';
        }
    break;}
    case 'profile': {
        $status = false;

        }
        if(empty($_GET['page3']) || !$f2user->get_userExists($_GET['page3'])) {
            // No username given
            $status = true;
        }
        if(!$status) {
            $return = $f2review->get_reviewsFromUser($_GET['page3']);
        }

        $_tmp = $f2user -> get_userid($_GET['page3']);
        if ($_tmp['status'] == TRUE && !$status) {
            $udetails = $f2user -> get_details($_tmp['content']);
            $tmp = new DateTime($udetails['content']['last_active']);
            $ctmp = new DateTime($udetails['content']['created_on']);
            $udetails = $f2user -> get_details($_tmp['content']);
            $ltmp = $f2backend -> timePassed($tmp->format("d-m-Y H:i:s"));
//            $date = new DateTime($time);
//            $now = new DateTime();
//            $seconds = $now->format('U') - $date->format('U');

            $now = new DateTime("now");
            $activeCheck = $now->modify("-5 seconds"); // Make sure this matches with the ticker

            if ($tmp >= $activeCheck) { // Has it been less than or equal to 5 seconds ago that user was last active?
                $ltmp = 'Online';
            }



            // $tmp = $f2user -> get_userdetail($tmp);
            $body .= '
                <div class="row">
                    <div class="col-12">
                        <h2 class="large">'.$_GET['page3'].'\'s Profile</h2>
                    </div>
                        <div class="col-8 list profile">


            ';

            $body .= '<div class="profile detail">
                        <h2 class="profile detail head">Details</h2>
                        <table>
                            <tbody>
                                <tr>
                                    <td width="120" class="lightLink">Last Online:</td>
                                    <td>'.$ltmp.'</td>
                                </tr>

                                <tr>
                                    <td class="lightLink">Join Date:</td>
                                    <td>'.$ctmp->format("d-m-Y").'</td>
                                </tr>

                                <tr>
                                    <td class="lightLink">User Title:</td>
                                    <td>'.$udetails['content']['user_rank'].'</td>
                                </tr>

                                <tr>
                                 <td class="lightLink">Acces Rank:</td>
                                    <td>'.$f2user->get_rankTitle($_tmp['content'])['content'].'</td>
                                </tr>

                                 <tr>
                                 <td class="lightLink">Reputation earned:</td>
                                    <td>'.$udetails['content']['reputation'].'</td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
';

                $body .='
                    </div>
                        <div class="col-4 profileCols">
                            <p><a class="username-small" href="'.$config['start_url'].'user/profile/' .$_GET['page3'].'">'.$_GET['page3'].'</a></p>
                            <section>'.$f2user->get_rankTitle($_tmp['content'])['content'].'</section>
                            <section>'.$udetails['content']['user_rank'].'</section>
                            <img class="profilePics" src="'.$f2user->get_useravatar($f2user->get_userid($_GET['page3'])['content'])['content'].'">
                        </div>
                        ';

                            $body .= '</div><div class="row list profile">
                                <div class="col-8">
                                    <h2>Reviews by '.$_GET['page3'].'</h2>
                                    ';
                            if($return['status']) {
                                $reviews = $return['content'];

                                foreach($reviews as $review) {
                                    //f2reviews.id, f2reviews.title, f2reviews.review, f2users.username, f2item.item_name, f2reviews.created_on
                                    $body .= '<section><a><a class="itemname-small" href="'.$config['start_url'].'review/' .$review['id'].'">'.$review['title'].' ('.$review['rating'].' / 10) </a> <i class="fa fa-angle-right"></i> <a class="itemname-small" href="'.$config['start_url'].'item/'.$review['type_name'].'/' .urlencode($review['item_name']).'"> '.$review['item_name'].' ('.$review['type_name'].')</a> <span>'.$f2backend->timePassed($review['created_on']).'</span></section>';
                                }
                            }
                            if(count($return['content']) < 1) {
                                $body .= '<div class="message warning"><h3>This user does not have any reviews.</h3></div>';
                            }

                   $body.= '</div></div>';


    break;
        } else {
            if (empty($_GET['page3'])) {
           $body .= '
                <div class="container">
                    <div class="row">
                        <div class="usernot">
                            <div class="message warning">
                                 <h3>No user selected or found, try search differently.</h3>
                            </div>
                        </div>
                    </div>
                </div>
                ';
        } else {
            $body .= '
                <div class="container">
                    <div class="row">
                        <div class="usernot">
                            <div class="message warning">
                                 <h3>The user that you\'re looking for does not exsist</h3>
                            </div>
                        </div>
                    </div>
                </div>
                ';
                }
        }
    break;}

