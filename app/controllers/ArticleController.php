<?php
use Phalcon\Http\Request;

// use form
use App\Forms\Article\CreateArticleForm;
use Phalcon\Db\Column;

class ArticleController extends ControllerBase
{
    public $createArticleForm;
    public $articleModel;
    
    public function initialize()
    {
        # Check User isLogin
        $this->authorized();
        $this->createArticleForm = new CreateArticleForm();
        $this->articleModel = new Articles();
    }

    /**
     * Create Article
     */
    public function createAction()
    {
        /**
         * Changing dynamically the Document Title
         * ------------------------------------------
         * @setTitle()
         * @prependTitle()
         */
        $this->tag->setTitle('Phalcon :: Add Article');
        // Login Form
        $this->view->form = new CreateArticleForm();
    }

    /**
     * Create Article Action
     * @method: POST
     * @param: title
     * @param: description
     */
    public function createSubmitAction()
    {
        // check request
        if (!$this->request->isPost()) {
            return $this->response->redirect('user/login');
        }

        # https://docs.phalconphp.com/en/3.3/security#csrf

        // Validate CSRF token
        if (!$this->security->checkToken()) {
            $this->flashSession->error("Invalid Token");
            return $this->response->redirect('article/create');
        }

        # Article Form with Model
        $this->createArticleForm->bind($_POST, $this->articleModel);
        # Check Form Validation
        if (!$this->createArticleForm->isValid()) {
            foreach ($this->createArticleForm->getMessages() as $message) {
                $this->flashSession->error($message);
                $this->dispatcher->forward([
                    'controller' => $this->router->getControllerName(),
                    'action'     => 'create',
                ]);
                return;
            }
        }

        # Article Set Save/Publish Value
        if ($this->request->getPost('publish') != NULL) {
            // Article Publish
            $this->articleModel->setIsPublic(1);

        } else {
            // Article Save Draft
            $this->articleModel->setIsPublic(0);
        }

        $this->articleModel->setUserId($this->session->get('AUTH_ID'));
        $this->articleModel->setCreated(time());
        $this->articleModel->setUpdated(time());

        # Doc :: https://docs.phalconphp.com/en/3.3/db-models#create-update-records
        if (!$this->articleModel->save()) {
            foreach ($this->articleModel->getMessages() as $m) {
                $this->flashSession->error($m);
                $this->dispatcher->forward([
                    'controller' => $this->router->getControllerName(),
                    'action'     => 'create',
                ]);
                return;
            }
        }

        $this->flashSession->success('Article successfully saved.');
        return $this->response->redirect('article/create');

        $this->view->disable();
    }

    /**
     * Manage Articles
     */
    public function manageAction()
    {
        $this->tag->setTitle('Phalcon :: Manage Articles');

        // Fetch All User Articles
        $articles = Articles::find([
            'conditions' => 'user_id = ?1',
            'bind'       => [
                1 => $this->session->get('AUTH_ID'),
            ],
            // 'columns' => 'id, title',
        ]);


        /**
         * Send Data in View Template
         * --------------------------------------------------------
         * $this->view->articlesdata = "Auth ID";
         * $this->view->setVars(['articlesdata' => "Auth ID"]);
         */
        $this->view->articlesData = $articles;

        # View Page Disable
        // $this->view->disable();
    }

    /**
     * Edit Article
     */
    public function editAction($articleId = null)
    {
        $url_id = urldecode(strtr($articleId,"'",'%'));
        $articleId = $this->crypt->decryptBase64($url_id);

        // Set Page Title
        $this->tag->setTitle('Phalcon :: Edit Article');

        // Check article id not empty
        if (!empty($articleId) AND $articleId != null)
        {
            // Check Post Request
            if($this->request->isPost())
            {
                # bind user type data
                $this->createArticleForm->bind($this->request->getPost(), $this->articleModel);
                $this->view->form = new CreateArticleForm($this->articleModel, [
                    "edit" => true
                ]);
                
            } else
            {
                // Fetch User Article
                $article = Articles::findFirst([
                    'conditions' => 'id = :1: AND user_id = :2:',
                    'bind' => [
                        '1' => $articleId,
                        '2' => $this->session->get('AUTH_ID')
                    ]
                ]);
                
                if (!$article) {
                    $this->flashSession->error('Article was not found');
                    return $this->response->redirect('article/create');
                }

                // Send Article Data in Article Form
                $this->view->form = new CreateArticleForm($article, [
                    "edit" => true
                ]);
            }
        } else {
            return $this->response->redirect('article/manage');
        }
    }

    /**
     * Edit Article Action Submit
     * @method: POST
     * @param: title
     * @param: description
     */
    public function editSubmitAction()
    {
        // check post request
        if (!$this->request->isPost()) {
            return $this->response->redirect('article/manage');
        }

        // Validate CSRT Token
        if (!$this->security->checkToken()) {
            $this->flashSession->error("Invalid Token");
            return $this->response->redirect('article/manage');
        }

        // get article id
        $articleEID = $this->request->getPost("eid");

        /**
         * Decode Article Eid
         */
        $articleID = $this->crypt->decryptBase64(urldecode(strtr($articleEID,"'",'%')));

        // Check Agin User Article is Valid
        $article = Articles::findFirst([
            'conditions' => 'id = :1: AND user_id = :2:',
            'bind' => [
                '1' => $articleID,
                '2' => $this->session->get('AUTH_ID')
            ]
        ]);

        if (!$article) {
            $this->flashSession->error('Article was not found');
            return $this->response->redirect('article/create');
        }

        # Check Form Validation
        if (!$this->createArticleForm->isValid($this->request->getPost(), $article)) {
            foreach ($this->createArticleForm->getMessages() as $message) {
                $this->flashSession->error($message);
                return $this->dispatcher->forward([
                    'controller' => $this->router->getControllerName(),
                    'action'     => 'edit',
                    'params' => [$articleID]
                ]);
            }
        }

        // Set Article New Data

        # Article Set Save/Publish Value
        if ($this->request->getPost('publish') != NULL) {
            // Article Publish
            $this->articleModel->setIsPublic(1);

        } else {
            // Article Save Draft
            $this->articleModel->setIsPublic(0);
        }

        // article id set
        $this->articleModel->setId($articleID);
        $this->articleModel->setUserId($this->session->get('AUTH_ID'));
        // $this->articleModel->setCreated(time());
        $this->articleModel->setUpdated(time());

        # Doc :: https://docs.phalconphp.com/en/3.3/db-models#create-update-records
        if ($this->articleModel->save($_POST) === false) {
            foreach ($this->articleModel->getMessages() as $m) {
                $this->flashSession->error($m);
            }

            return $this->dispatcher->forward([
                'controller' => $this->router->getControllerName(),
                'action'     => 'edit',
            ]);
        }

        // Clear Article Form
        $this->createArticleForm->clear();

        $this->flashSession->success('Article was updated successfully.');
        return $this->response->redirect('article/manage');

        $this->view->disable();
    }

    /**
     * Delete Article
     */
    public function deleteAction($articleEID)
    {
        /**
         * Decode Article EID
         * ----------------------------------------------------
         * http://php.net/manual/en/function.urlencode.php
         */
        $articleID = $this->crypt->decryptBase64(urldecode(strtr($articleEID,"'",'%')));

        $id = (int) $articleID;
        if ($id > 0 AND !empty($id))
        {
            // Check Agin User Article is Valid
            $article = Articles::findFirst([
                'conditions' => 'id = :1: AND user_id = :2:',
                'bind' => [
                    '1' => $id,
                    '2' => $this->session->get('AUTH_ID')
                ]
            ]);

            if (!$article) {
                $this->flashSession->error('Article was not found');
                return $this->response->redirect('article/manage');
            }    

            if (!$article->delete()) {
                foreach ($article->getMessages() as $msg) {
                    $this->flashSession->error((string) $msg);
                }
                return $this->response->redirect("article/manage");
            } else {
                $this->flashSession->success("Article was deleted");
                return $this->response->redirect("article/manage");
            }

        } else {
            $this->flashSession->error("Article ID Invalid.");
            return $this->response->redirect("article/manage");
        }

        # View Page Disable
        $this->view->disable();
    }
}