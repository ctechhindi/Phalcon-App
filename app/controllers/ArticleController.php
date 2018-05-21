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
        # ---------------------------------------------------------------------------------------------------
        // $articles = Articles::find("is_public = '1'");
        $articles = Articles::find(
            [
                'conditions' => 'title = ?1',
                'bind'       => [
                    1 => 'Article Title Second',
                ],
                'columns' => 'id, title',
            ]
        );

        foreach ($articles as $article) {
            echo "<br>";
            echo $article->id .', Title :'.$article->title, "\n";
        }
        # ---------------------------------------------------------------------------------------------------
        

        # ---------------------------------------------------------------------------------------------------
        $articles = Articles::query()
            ->where('title = :title:')
            ->andWhere('year < 2000')
            ->bind(['title' => 'Article Title'])
            ->order('name')
            ->execute();

        foreach ($articles as $article) {
            echo "<br>";
            echo $article->id .', Title :'.$article->title, "\n";
        }
        # ---------------------------------------------------------------------------------------------------
        
        
        # ---------------------------------------------------------------------------------------------------
        $title = 'Article Title Second';

        $article = Articles::findFirstByTitle($title);
        // $article->count();
        if ($article) {
            echo 'The first article with the name ' . $title . ' cost ' . $article->title . '. ID '. $article->id;
        } else {
            echo 'There were no articles found in our table with the name ' . $title . '.';
        }
        # ---------------------------------------------------------------------------------------------------
        

        # ---------------------------------------------------------------------------------------------------
        // Bind parameters
        $parameters = [
            'title' => 'Article Title',
            'is_public' => 'Article Title',
        ];

        // Casting Types
        $types = [
            'title' => Column::BIND_PARAM_STR,
            'is_public' => Column::BIND_PARAM_INT,
        ];

        // Query robots binding parameters with string placeholders
        $articles = Articles::find(
            [
                'title = :title: AND is_public = :is_public:',
                'bind'      => $parameters,
                'bindTypes' => $types,
            ]
        );

        foreach ($articles as $article) {
            echo "<br>";
            echo $article->id .', Title :'.$article->title, "\n";
        }
        # ---------------------------------------------------------------------------------------------------

        exit;
    }
}