(function () {

var myArea,
    wrap = document.getElementById('kz-mg-runner-wrap'),
    loaderText,
    bg,
    runner,
    score,
    level,
    goal,
    ground,
    retry,
    retryText,
    ctx,
    thanksPage = wrap.getAttribute('data-thanks'),
    secondTouch = false,
    obs = [],
    newObs,
    runnerX = 80,
    runnerImg1 = wrap.getAttribute('data-img1'),
    runnerImg2 = wrap.getAttribute('data-img2'),
    runnerJump = wrap.getAttribute('data-runner-jump'),
    runnerGoal = wrap.getAttribute('data-runner-goal'),
    runnerWidth = 40,
    runnerHeight = 50,
    goalImg = wrap.getAttribute('data-goal'),
    goalWidth = 70,
    obs1Img = wrap.getAttribute('data-obs1'),
    obs2Img = wrap.getAttribute('data-obs2'),
    bgImg = wrap.getAttribute('data-bg'),
    textColor = wrap.getAttribute('data-text'),
    groundColor = wrap.getAttribute('data-ground'),
    finalLevel = Number(wrap.getAttribute('data-final-level')),
    afterGoal = 20,
    loadChecker = 0,
    urls = [runnerImg1, runnerImg2, runnerJump, runnerGoal, goalImg, obs1Img, obs2Img, bgImg],
    sns = document.getElementById('kz-mg-runner-sns');

myArea = {
    canvas: document.getElementById('kz-mg-runner'),
    init: function () {
        this.canvas.width = 600;
        this.canvas.height = 320;
        this.context = this.canvas.getContext('2d');
        this.frameNo = 0;
        this.goalFrame = 0;
        this.goalCheck = false;
        this.nextObsFrame = 0;
        this.jumpsForNext = 4;
        this.obsCreated = 0;
        this.jumped = 0;
        this.level = 1;
        this.levelUp = false;
        this.status = 'not loaded';
        this.finalLevel = finalLevel;
    },
    start: function () {
        this.interval = setInterval(updateArea, 20);
    },
    clear: function () {
        this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
    },
    stop: function () {
        clearInterval(this.interval);
        if (this.status != 'goal') this.status = 'stop';
    },
    loader: function () {
        this.loaderInterval = setInterval(loader, 20);
    },
    loaderEnd: function () {
        clearInterval(this.loaderInterval);
        this.status = 'loaded';
    }
}

window.addEventListener('DOMContentLoaded', function () {

    myArea.init();

    // Load all the images before staring the game
    for (var i = 0; i < urls.length; i++) {
        var img = new Image();
        img.src = urls[i];
        img.addEventListener('load', function () {
            loadChecker++;
        });
        document.getElementById('kz-mg-runner-loader').appendChild(img);
    }

    // Check the loading status
    var loadCheckerInt = setInterval(function () {
        if (loadChecker == urls.length) {
            myArea.loaderEnd();
            myArea.status = 'start';
            startGame();
            clearInterval(loadCheckerInt);
        } else {
            if (myArea.status != 'loading') {
                myArea.status = 'loading';
                loaderText = new component('20px', 'Consolas', textColor, ((wrap.offsetWidth / 2) - 60), 180, 'text');
                myArea.loader();
            }
        }
    }, 50)
});

/**
 * @desc   Clear the canvas to draw loader text
 */
function loader() {
    myArea.clear();
    loaderText.text = 'loading ' + Math.floor(loadChecker / urls.length * 100) + '%';
    loaderText.update();
}

/**
 * @desc   Set important components and get ready to start.
 *         In case of restart, start playing immediately.
 * @param  bool restart
 */
function startGame(restart) {
    myArea.clear();
    bg = new component(myArea.canvas.width, myArea.canvas.height - 20, bgImg, 0, 0, 'bg');
    if (wrap.offsetWidth <= 640) runnerX = 40;
    runner = new component(runnerWidth, runnerHeight, runnerImg1, runnerX, myArea.canvas.height - 150, 'image', true);
    score = new component('30px', 'Consolas', textColor, 10, 40, 'text');
    score.text = 'SCORE: 0';
    level = new component('20px', 'Consolas', textColor, 10, 70, 'text');
    retry = new component('20px', 'Consolas', textColor, ((wrap.offsetWidth / 2) - 30), 180, 'text');
    retryText = new component('16px', 'Consolas', textColor, ((wrap.offsetWidth / 2) - 70), 200, 'text');
    goal = new component(goalWidth, myArea.canvas.height - 20, goalImg, myArea.canvas.width, myArea.canvas.height - 220, 'image');
    ground = new component(myArea.canvas.width, 20, groundColor, 0, myArea.canvas.height - 20);
    level.text = 'Stage ' + myArea.level;
    bg.update();
    goal.update();
    runner.update();
    ground.update();
    if (restart) startPlaying();
}

// List of event listeners
window.addEventListener('keydown', function (e) {
    if (e.keyCode == 32) e.preventDefault();
    if (myArea.status == 'start' && e.keyCode == 32) startPlaying();
    if (myArea.status == 'playing' && e.keyCode == 32) jump();
});
window.addEventListener('keyup', function (e) {
    if (e.keyCode == 82) e.preventDefault();
    if (myArea.status == 'stop' && e.keyCode == 82) restartGame();
})
window.addEventListener('mousedown', function (e) {
    if (e.target.id == 'kz-mg-runner' || e.target.id == 'kz-mg-runner-goal-text') {
        if (myArea.status == 'start') startPlaying();
        if (myArea.status == 'playing') jump();
        if (myArea.status == 'goal') {
            // Simulate a POST with Form
            var form = document.createElement('form');
            form.setAttribute('action', thanksPage);
            form.setAttribute('method', 'post');
            form.id = 'kz-mg-runner-form';
            form.style.display = 'none';
            document.body.appendChild(form);
            var finalScore = document.createElement('input');
            finalScore.setAttribute('type', 'hidden');
            finalScore.setAttribute('name', 'score');
            finalScore.setAttribute('value', (myArea.frameNo / 4) * 10);
            form.appendChild(finalScore);
            var finalFrame = document.createElement('input');
            finalFrame.setAttribute('type', 'hidden');
            finalFrame.setAttribute('name', 'score');
            finalFrame.setAttribute('value', myArea.frameNo);
            form.appendChild(finalFrame);
            var goalImage = document.createElement('input');
            goalImage.setAttribute('type', 'hidden');
            goalImage.setAttribute('name', 'image');
            goalImage.setAttribute('value', myArea.canvas.toDataURL());
            form.appendChild(goalImage);
            form.appendChild(document.getElementById('kz-mg-runner-nonce'));
            form.submit();
        }
    }
});
window.addEventListener('dblclick', function (e) {
    if (e.target.id == 'kz-mg-runner') {
        if (myArea.status == 'stop') restartGame();
    }
});
window.addEventListener('touchstart', function () {
    // Logic to detect double tap
    if (!secondTouch) {
        secondTouch = true;
        setTimeout(function () {
            secondTouch = false
        }, 300);
        return false;
    }
    if (myArea.status == 'stop') restartGame();
})

/**
 * @desc   Restart obstacles, the level and frame number.
 */
function restartGame() {
    obs = [];
    myArea.level = 1;
    myArea.frameNo = 0;
    startGame(true);
}

/**
 * @desc   Start playing the game
 */
function startPlaying() {
    sns.className = 'off';
    myArea.status = 'playing';
    myArea.start();
}

/**
 * @desc   Create a compnent for the game.
 *         Components that can be created are images and texts
 * @param  int/str w - width. Font size in case of a text
 * @param  int/str h - height. Font family in case of a text.
 * @param  str c - color. Source of file in case of an image. 
 * @param  int x - x coordinate
 * @param  int y - y coordinate
 * @param  str type - text, image or bg (background)
 * @param  bool runnerObj - true if the component is a runner
 */
function component(w, h, c, x, y, type, runnerObj) {
    this.type = type;
    if (type == 'image' || type == 'bg') {
        this.image = new Image();
        this.image.src = c;
    }
    this.w = w;
    this.h = h;
    this.spX = 0;
    this.spY = 0;
    this.gravity = 0.5;
    this.gravitySp = 0;
    this.x = x;
    this.y = y;
    this.running = false;
    this.runningStatus = 1;
    this.update = function () {
        ctx = myArea.context;
        if (this.type == 'image') {
            if (runnerObj) {
                if (myArea.status == 'goal') {
                    this.image.src = runnerGoal;
                } else {
                    if (this.running) {
                        // Toggle the image of the runner
                        if (myArea.frameNo % 8 == 0) {
                            switch (this.runningStatus) {
                                case 1:
                                    this.runningStatus = 2;
                                    break;
                                case 2:
                                    this.runningStatus = 1;
                                    break;
                            }
                        }
                        switch (this.runningStatus) {
                            case 1:
                                this.image.src = runnerImg1;
                                break;
                            case 2:
                                this.image.src = runnerImg2;
                                break;
                        }
                    } else {
                        this.image.src = runnerJump;
                    }
                }
            }
            ctx.drawImage(this.image, this.x, this.y, this.w, this.h);
        } else if (this.type == 'bg') {
            // Draw the background image twice to simulate the movement
            ctx.drawImage(this.image, this.x, this.y, this.w, this.h);
            ctx.drawImage(this.image, this.x + this.w, this.y, this.w, this.h);
        } else {
            ctx.fillStyle = c;
            if (this.type == 'text') {
                ctx.font = this.w + ' ' + this.h;
                ctx.fillText(this.text, this.x, this.y);
            } else {
                ctx.fillRect(this.x, this.y, this.w, this.h);
            }
        }
    }
    this.newPos = function () {
        // Calculate the new position of the component
        this.gravitySp += this.gravity;
        this.x += this.spX;
        if (this.type != 'bg') this.y += this.spY + this.gravitySp;
        if (runnerObj) {
            this.hitBtm();
            this.jumpTop();
        }
        if (this.type == 'bg') {
            if (this.x == -(this.w)) {
                this.x = 0;
            }
        }
    }
    this.hitBtm = function () {
        // Check whether the runner hit the bottom
        var rockbtm = myArea.canvas.height - 20 - this.h;
        if (this.y > rockbtm) {
            this.y = rockbtm;
            this.gravitySp = 0;
            this.running = true;
        }
    }
    this.jumpTop = function () {
        // Check whether the runner reached the highest point
        var jumpTop = myArea.canvas.height - 56 - this.h;
        if (this.running == false && this.y < jumpTop) {
            this.gravity = 0.6;
        }
    }
    this.frameOut = function () {
        // Check whether the component (a obstacle) has framed out
        if (this.x < (0 - this.w)) return true;
        return false;
    }
    this.crashWith = function (obj) {
        // Check whether the component has crashed with obj (component or goal)
        var l = this.x + 5,
            r = this.x + (this.w) - 5,
            t = this.y + 5,
            b = this.y + (this.h) - 5,
            l2 = obj.x,
            r2 = obj.x + (obj.w),
            t2 = obj.y,
            b2 = obj.y + (obj.h),
            crash = true;
        if (b < t2 || t > b2 || r < l2 || l > r2) crash = false;
        return crash;
    }
}

/**
 * @desc   Update the area in every frame
 * @return n/a
 */
function updateArea() {
    var x = myArea.canvas.width,
        y = myArea.canvas.height - 60;
    
    for (i = 0; i < obs.length; i++) {
        // Has the runner crashed with an obstacle?
        if (runner.crashWith(obs[i])) {
            retry.text = 'Retry?';
            retry.update();
            if (isMobile()) {
                retryText.text = 'Double tap to retry.';
            } else {
                retryText.text = 'Double click to retry.';
            }
            retryText.update();
            myArea.stop();
            document.getElementById('kz-mg-runner-sns-save').setAttribute('href', myArea.canvas.toDataURL());
            sns.className = 'on';
            return;
        } else if (obs[i].frameOut()) {
            // Remove an obstacle that has framed out
            obs.splice(i, 1);
            i--;
        }
    }

    if (myArea.obsCreated >= myArea.jumpsForNext) {
        myArea.obsCreated = 0;
        myArea.level++;
    }

    if (myArea.level == myArea.finalLevel + 1 && obs.length == 0) {
        //Goal
        
        level.text = 'GOAL!';
        
        goal.spX = 3 * (1 + myArea.level * 0.2)
        goal.x -= goal.spX;
        
        if (runner.crashWith(goal) && myArea.goalCheck == false) {
            myArea.goalFrame = myArea.frameNo;
            myArea.goalCheck = true;
        }
    } else {
        // Still in game
        if (myArea.level < myArea.finalLevel + 1) {
            if ((myArea.frameNo == 50 || myArea.frameNo == myArea.nextObsFrame)) {
                // Create the next obstacle
                // Randomly choose an image for the new obstacle
                if (Math.floor(Math.random() * 10) % 2 == 0) {
                    newObs = new component(20, 40, obs1Img, x, y, 'image');
                } else {
                    newObs = new component(20, 40, obs2Img, x, y, 'image');
                }
                newObs.spX = 3 * (1 + myArea.level * 0.2);
                obs.push(newObs);
                nextObsFrame(100);
                myArea.obsCreated++;
            }
            level.text = 'Stage ' + myArea.level;
        } else {
            level.text = 'Stage ' + (myArea.level - 1);
        }
    }

    myArea.clear(); // Need to clear the frame
    myArea.frameNo += 1;
    bg.spX = -1;
    bg.newPos();
    bg.update();
    if (myArea.frameNo % 4 == 0) {
        score.text = 'SCORE: ' + ((myArea.frameNo / 4) * 10);
    }


    // how many frames to run after touching the goal component
    if (window.innerWidth <= 640) {
        afterGoal = 18 - myArea.level * 1.2;
    } else {
        afterGoal = 20 - myArea.level * 1.5;
    }

    if (myArea.goalFrame != 0 && myArea.frameNo >= myArea.goalFrame + afterGoal) {
        // If the runner has run for some frames after touching the goal component
        myArea.status = 'goal';
        myArea.clear();
        bg.update();
        goal.update();
        score.update();
        level.update();
        ground.update();
        runner.update();
        myArea.stop();
        document.getElementById('kz-mg-runner-sns-save').setAttribute('href', myArea.canvas.toDataURL());
        sns.className = 'on';
        wrap.setAttribute('data-status', 'goal');
        return;
    } else {
        goal.update();
        score.update();
        level.update();
        for(i = 0; i < obs.length; i++) {
            obs[i].x -= obs[i].spX;
            obs[i].update();
        }
        runner.newPos();
        runner.update();
        ground.update();
    }
}

/**
 * @desc   Create a random number for the next obstacle and set the number to myArea.nextObsFrame
 * @param  int n - the basic number of frames to create the next obstacle
 */
function nextObsFrame(n) {
    var min = 60 * (1 + (myArea.level - 1) * 0.04),
        max = min * 1.8;
    n = n - (6.8 * (myArea.level - 1));
    myArea.nextObsFrame = myArea.frameNo + Math.floor(n * Math.floor(Math.random() * (max - min) + min) * 0.01);
}

/**
 * @desc   Make the runner jump
 */
function jump() {
    if (runner.running == true) {
        runner.gravity = -0.6;
        runner.running = false;
    }
}

/**
 * @desc   Detect the mobile
 * @return bool - true if the user is using a mobile browser
 */
function isMobile() {
    if (navigator.userAgent.match(/Android/i) ||
        navigator.userAgent.match(/webOS/i) ||
        navigator.userAgent.match(/iPhone/i) ||
        navigator.userAgent.match(/iPad/i) ||
        navigator.userAgent.match(/iPod/i) ||
        navigator.userAgent.match(/BlackBerry/i) ||
        navigator.userAgent.match(/Windows Phone/i)
    ) {
        return true;
    } else {
        return false;
    }
}
})();
