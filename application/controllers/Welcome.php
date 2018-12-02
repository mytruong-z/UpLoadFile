<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {
    private $_uploaded;
    public function __construct()
    {
        parent::__construct();
        $this->load->library('upload');
        $this->load->library('form_validation');
    }
    public  function index()
    {
        $this->load->helper('form');
        $this->load->library('upload');
        $this->load->library('form_validation');
        $data['title'] = 'Multiple file upload';
        $this->load->library('form_validation');
        $this->form_validation->set_rules('uploadedimages[]','Upload image','callback_fileupload_check');
        if($this->input->post())
        {
            if($this->form_validation->run())
            {
                $created_images = array();
                foreach($this->_uploaded as $key => $source_image)
                {
                    $new_images = $this->_image_creation($source_image);
                    $created_images[$key] = $new_images;
                }
                echo '<pre>';
                print_r($created_images);
                echo '</pre>';
                exit;
            }
        }

        $this->load->view('upload_form',$data);
    }

    public function fileupload_check()
    {
        $this->load->library('upload');
        $this->load->library('form_validation');
        $number_of_files = sizeof($_FILES['uploadedimages']['tmp_name']);
        $files = $_FILES['uploadedimages'];
        for($i=0;$i<$number_of_files;$i++)
        {
            if($_FILES['uploadedimages']['error'][$i] != 0)
            {
                $this->form_validation->set_message('fileupload_check', 'Couldn\'t upload the file(s)');
                return FALSE;
            }
        }
        $this->load->library('upload');
        $config['upload_path'] = FCPATH.'upload/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        for($i = 0;$i<$number_of_files;$i++)
        {
            $_FILES['uploadedimage']['name'] = $files['name'][$i];
            $_FILES['uploadedimage']['type'] = $files['type'][$i];
            $_FILES['uploadedimage']['tmp_name'] = $files['tmp_name'][$i];
            $_FILES['uploadedimage']['error'] = $files['error'][$i];
            $_FILES['uploadedimage']['size'] = $files['size'][$i];


            $this->upload->initialize($config);
            $this->load->library('upload',$config);
            if($this->upload->do_upload('uploadedimage'))
            {
                $this->_uploaded[$i] = $this->upload->data();
            }
            else
            {
                $this->form_validation->set_message('fileupload_check', $this->upload->display_errors());
                return FALSE;
            }
        }
        return TRUE;
    }
    private function _image_creation($image)
    {
        if(!is_array($image)||empty($image))
        {
            return FALSE;
        }
        if($image['is_image'] != 1)
        {
            return FALSE;
        }
        $new_images =array();

        $image_width = 620;
        $image_height = 200;
        $thumb_width = 100;
        $thumb_height = 100;
        $thumb_name = '-thumb';

        $gallery_path = FCPATH.'media/galleries/';

        $this->load->library('image_lib');
        $errors = array();
        $config['image_library'] = 'gd2';
        $config['source_image'] = $image['full_path'];
        $config['maintain_ratio'] = FALSE;

        $source_ratio = $image['image_width']/$image['image_height'];
        $new_ratio = $image_width/$image_height;

        if($source_ratio != $new_ratio)
        {
            if($new_ratio > $source_ratio || (($new_ratio == 1) && ($source_ratio < 1)))
            {
                $config['width'] = $image['image_width'];
                $config['height'] = round($image['image_width']/$new_ratio);
                $config['y_axis'] = round(($image['image_height'] - $config['height'])/2);
                $config['x_axis'] = 0;
            }
            else
            {
                $config['width'] = round($image['image_height'] * $new_ratio);
                $config['height'] = $image['image_height'];
                $size_config['x_axis'] = round(($image['image_width'] - $config['width'])/2);
                $size_config['y_axis'] = 0;
            }
        }
        $image_path = $gallery_path.$image['file_name'];
        $thumb_path = $gallery_path.$image['raw_name'].$thumb_name.$image['file_ext'];
        $new_file = $image['file_name'];
        $new_thumb = $image['raw_name'].$thumb_name.$image['file_ext'];
        if(file_exists($image_path)|| file_exists($thumb_path))
        {
            for($i=1;$i<=100;$i++)
            {
                $new_file = $image['raw_name'].'-'.$i.$image['file_ext'];
                $new_thumb = $image['raw_name'].'-'.$i.$thumb_name.$image['file_ext'];
                if(!file_exists($new_file))
                {
                    $image_path = $gallery_path.$new_file;
                    $thumb_path = $gallery_path.$new_thumb;
                }
            }
        }
        $config['new_image'] = $image_path;
        $config['quality'] = '100%';
        $this->image_lib->initialize($config);
        if(!$this->image_lib->crop())
        {
            $errors[] = $this->image_lib->display_errors();
        }

        $this->image_lib->clear();

        $config['maintain_ratio'] = TRUE;
        $config['source_image'] = $image_path;
        $config['width'] = $image_width;
        $config['height'] = $image_height;

        $config['quality'] = '70%';
        $this->image_lib->initialize($config);
        if(!$this->image_lib->resize())
        {
            $errors[]= $this->image_lib->display_errors();
        }
        $this->image_lib->clear();
        $new_images['image'] = array('file_name'=>$new_file,'path'=>$config['new_image'],'errors'=>$errors);

        $errors = array();
        $config['source_image'] = $config['new_image'];

        $source_ratio = $image['image_width'] / $image['image_height'];
        $new_ratio = $thumb_width / $thumb_height;
        if($source_ratio!=$new_ratio)
        {
            if($new_ratio > $source_ratio || (($new_ratio == 1) && ($source_ratio < 1)))
            {
                $config['width'] = $image['image_width'];
                $config['height'] = round($image['image_width']/$new_ratio);
                $config['y_axis'] = round(($image['image_height'] - $config['height'])/2);
                $config['x_axis'] = 0;
            }
            else
            {
                $config['width'] = round($image['image_height'] * $new_ratio);
                $config['height'] = $image['image_height'];
                $size_config['x_axis'] = round(($image['image_width'] - $config['width'])/2);
                $size_config['y_axis'] = 0;
            }
        }
        $config['new_image'] = $thumb_path;
        $config['quality'] = '100%';
        $this->image_lib->initialize($config);
        if(!$this->image_lib->crop())
        {
            $errors[] = $this->image_lib->display_errors();
        }

        $this->image_lib->clear();

        $config['maintain_ratio'] = TRUE;
        $config['source_image'] = $thumb_path;
        $config['width'] = $thumb_width;
        $config['height'] = $thumb_height;
        $config['quality'] = '70%';
        $this->image_lib->initialize($config);
        if(!$this->image_lib->resize())
        {
            $errors[] = $this->image_lib->display_errors();
        }
        $this->image_lib->clear();

        $new_images['thumb'] = array('file_name'=>$new_thumb,'path'=>$config['new_image'],'errors'=>$errors);
        return $new_images;
    }

}
