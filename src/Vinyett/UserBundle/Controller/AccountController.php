<?php

namespace Vinyett\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use JMS\SecurityExtraBundle\Annotation\Secure;

use Symfony\Component\Validator\Constraints as Assert;

use Vinyett\UserBundle\Form\Type as AccountTypes;

use Vinyett\UserBundle\Object\PhotoIcon;


class AccountController extends Controller
{

    /**
     * Account index
     *
     * @Secure(roles="ROLE_USER")
     */        
    public function indexAction()
    {
        $user = $this->get("security.context")->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $updated_info = false;
        
        $form = $this->createForm(new AccountTypes\AccountType(), $user);
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
    
            if ($form->isValid()) {
                $em->persist($user);
                $em->flush();
                
                $updated_info = true;
            }
        }
    
        return $this->render('UserBundle:Account:account_new.html.twig', array("updated_info" => $updated_info, "form" => $form->createView()));
    }

    /**
     * Account - update password
     *
     * @Secure(roles="ROLE_USER")
     */        
    public function updatePasswordAction()
    {
        $user = $this->get("security.context")->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $updated_info = false;
        
        $form = $this->createForm(new AccountTypes\PasswordType(), $user);
        
        if ($request->isMethod('POST')) {
            $form->bind($request);
    
            if ($form->isValid()) {
                //$userAdmin->setPlainPassword('test');
                
                $updated_info = true;
            }
        }
    
        return $this->render('UserBundle:Account:password.html.twig', array("updated_info" => $updated_info, "form" => $form->createView()));
    }
    
    /**
     * Removes a users icon and wipes it from the server.
     *
     * @Secure(roles="ROLE_USER")
     */        
    public function purgePhotoIconAction()
    {
        $user = $this->get("security.context")->getToken()->getUser();
        $logger = $this->get("logger");
        $em = $this->getDoctrine()->getEntityManager();
        $s3 = $this->get("aws_s3");
        $s3->set_region(\AmazonS3::REGION_OREGON);
                
        $user->setPhotoSquare(null);
        
        $em->flush();
        
        $r = $s3->delete_object("icons.vinyett.com", "farm/".$user->getId()."@icon.jpg");
        $logger->info($r->isOK());
        
        return $this->redirect($this->generateUrl("photostream", array("username" => $user->getUrlUsername())));
    }   
    
    /**
     * Tool to crop photo icon
     *
     * @Secure(roles="ROLE_USER")
     */   
    public function photoCropperAction()
    {
        $user = $this->get("security.context")->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $logger = $this->get("logger");
        $request = $this->getRequest();
        
        $form = $this->get('form.factory')->createNamedBuilder('cropper', 'form', array("x" => 0, "y" => 0, "h" => 100, "w" => 100), array())
                     ->add("x", "hidden", array(
                        'constraints' => array(
                           new Assert\NotBlank(),
                           new Assert\Type(array('type' => "numeric")),
                       ),
                     ))
                     ->add("y", "hidden", array(
                        'constraints' => array(
                           new Assert\NotBlank(),
                           new Assert\Type(array('type' => "numeric")),
                       ),
                     ))
                     ->add("h", "hidden", array(
                        'constraints' => array(
                           new Assert\NotBlank(),
                           new Assert\Type(array('type' => "numeric")),
                       ),
                     ))
                     ->add("w", "hidden", array(
                        'constraints' => array(
                           new Assert\NotBlank(),
                           new Assert\Type(array('type' => "numeric")),
                       ),
                     ))
                     ->getForm();
        
        //Handle form submissions
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if($form->isValid()) { 
                
                
                $positions = $form->getData();
                
            	$img_r = imagecreatefromjpeg("http://photos.vinyett.com/temporary_icon_farm/".$user->getId()."_uncropped_icon.jpg");
                $dst_r = ImageCreateTrueColor(150, 150);
            	imagecopyresampled($dst_r, $img_r, 0, 0, $positions['x'], $positions['y'], 150, 150, $positions['w'], $positions['h']);
                
                //Avoid saving this image to the local server...
                ob_start();
                imagejpeg($dst_r);
                $picture = ob_get_contents();
                ob_end_clean();
                
                //s3 init and upload
                $s3 = $this->get("aws_s3");
                $s3->set_region(\AmazonS3::REGION_OREGON);
                $s3->enable_path_style(true);
                
                $name = $user->getId()."@icon";
                
                $bucket = "icons.vinyett.com";
                
                $response = $s3->create_object($bucket, "farm/".$name.".jpg", array(
                    'body' => $picture,
                    'acl' => \AmazonS3::ACL_PUBLIC,
                    "contentType" => "image/jpeg"
                ));
                
                $r = $response->isOK();
                $logger->info($r);
                
                if ($r) {
                    //Don't forget to update the user object and remove the image from the photo farm
                    $user->setPhotoSquare("http://icons.vinyett.com/farm/".$user->getId()."@icon.jpg");
                    $r2 = $s3->delete_object("photos.vinyett.com", "temporary_icon_farm/".$user->getId()."_uncropped_icon.jpg");
                    $logger->info($r2->isOK());
                    
                    $em->flush();
                    
                    return $this->redirect($this->generateUrl("photostream", array("username" => $user->getUrlUsername())));
                } else { 
                    throw new \Exception('There was a problem connecting to S3 to dump images, try it again because Amazon is probably hiccuping.');
                }
            }
        }
        
        
        return $this->render('UserBundle:Account:cropper.html.twig', array("form" => $form->createView()));
    }
    
    /**
     * Receives the icon from somewhere and saves it temporarily
     *
     * @Secure(roles="ROLE_USER")
     */     
    public function cropPhotoIconAction()
    { 
        //takes the uploaded photo and displays it back in the cropper
        $photo_icon = new PhotoIcon(); 
        $pc = $this->get("photo.cruncher");
        $s3 = $this->get("aws_s3");
        $user = $this->get("security.context")->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $logger = $this->get("logger");
        $request = $this->getRequest();

        //bootstrap
        $s3->set_region(\AmazonS3::REGION_OREGON);
        $s3->enable_path_style(true);
        
        $form = $this->createFormBuilder(array(), array("csrf_protection" => false))
                     ->add("picture", "file", array("constraints" => array(new Assert\Image(array(
                            'minWidth' => 200,
                            'maxWidth' => 1000,
                            'minHeight' => 200,
                            'maxHeight' => 1000,
                        )),
                        new Assert\NotBlank()
                     )))
                     ->getForm();
        
        $form->bind(array("picture" => $request->files->get("picture")));
        if($form->isValid()) 
        { 
            //Let's display it back!
            $data = $form->getData();
            $picture = $data['picture'];
            
            //upload to s3...
            $name = $user->getId()."_uncropped_icon";
    
            // S3 bucket and filename
            $bucket = "photos.vinyett.com";
            
            $response = $s3->create_object($bucket, "temporary_icon_farm/".$name.".jpg", array(
                'fileUpload' => $picture,
                'acl' => \AmazonS3::ACL_PUBLIC,
                "contentType" => "image/jpeg"
            ));
            
            $r = $response->isOK();
            $logger->info($r);
            
            if ($r) {
                
                $_photopath = "http://photos.vinyett.com/temporary_icon_farm/".$name.".jpg";
                
                return $this->redirect($this->generateUrl("account_photo_crop"));
            } else {
                throw new \Exception('There was a problem connecting to S3 to dump images, try it again because Amazon is probably hiccuping.');
            }
                   
        } else { 
            /* set flash */
            throw new \Exception($form->getErrorsAsString());
            return $this->redirect($this->generateUrl("account"));
        }
        
    }
    
    
    /**
     * Updates your profile name.
     *
     * @Secure(roles="ROLE_USER")
     */ 
    public function ajaxUpdateNameAction(Request $request)
    { 
        $em = $this->getDoctrine()->getEntityManager();
        $factory = $this->get('security.encoder_factory');
        $user = $this->get("security.context")->getToken()->getUser();
        $form = $this->createForm(new NameType(), $user);
        
        $form->bindRequest($request);
        if($form->isValid())
        {
            
            $password = $request->get('name');
            //Lets see if the password is correct...
            $password = $factory->getEncoder($user)->encodePassword($password["password"], $user->getSalt());
            if($password != $user->getPassword())
            {
                $data = array("error" => array("was" => true, 
                                               "title" => "Wrong password", 
                                               "content" => "Your name wasn't updated because you typed your password incorrectly. Double check caps, remember that passwords are case sensitive, and try again."));
                //Oh my fucking god.
                goto ajax_name_finish;
            }
        
            $em->persist($user);
            $em->flush();
            
            $data = array("user"  => array("name" => 
                                                    array("first" => $user->getFirstName(), 
                                                          "last" => $user->getLastName(), 
                                                          "display" => $user->getFirstName()." ".$user->getLastName()
                                                          )
                                            )
                         );
        } else { 
            $data = array("error" => array("was" => true, "title" => "Something went wrong", "content" => array("errors" => $form->getErrors())));
        }
        
        //lajsdnfkjbklabsdfgjkhbajhksfgbkjsh bfjkhsflkbhsvdlak
        ajax_name_finish:
        
        return new Response(json_encode($data));
    }
    
}
