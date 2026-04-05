      
            document.addEventListener("DOMContentLoaded", function () {

                // Handle mobile view for chat selection
                window.addEventListener("chatSelected", handleChatSelection);

                // Listen for message sent event to scroll to bottom
                window.addEventListener("messageSent", scrollToBottom);

                // Listen for messages loaded more event to maintain scroll position
                window.addEventListener('messagesLoadedMore', handleLoadingMore);

                // TODO: Temporary chat selection for demo
                document.querySelectorAll("#contacts-list > div").forEach(item => {
                    item.addEventListener("click", () => {
                        document.querySelectorAll("#contacts-list > div").forEach(i => {
                            i.classList.remove("bg-zinc-750", "border-cyan-400");
                            i.classList.add("border-transparent");
                            i.querySelector(".user-image").classList.remove("ring-2", "ring-cyan-400");
                            i.querySelector(".time-box").classList.remove("text-cyan-400");
                            i.querySelector(".time-box").classList.add("text-zinc-500");
                        });
                        item.classList.add("bg-zinc-750", "border-cyan-400");
                        item.classList.remove("border-transparent");
                        item.querySelector(".user-image").classList.add("ring-2", "ring-cyan-400");
                        item.querySelector(".time-box").classList.add("text-cyan-400");
                        item.querySelector(".time-box").classList.remove("text-zinc-500");
                        handleChatSelection();
                    });
                });

                document.getElementById("contacts-list").firstElementChild.click();

                // Handle message input and send button
                function handleInput() {
                    let messageForm = document.getElementById("message-form");
                    let messageInput = document.getElementById("message-input");
                    let sendButton = document.getElementById("send-message-button");
                    
                    if (messageForm) {
                        messageInput.focus();
                        const hasText = v => v && v.trim();
                        const updateBtn = () => {
                            sendButton.disabled = !hasText(messageInput.value);
                            sendButton.classList.toggle('btn-disabled', sendButton.disabled);
                        };
                        updateBtn();
                        messageInput.addEventListener('input', updateBtn);
                        messageForm.onsubmit = e => {
                            e.preventDefault();
                            if (!hasText(messageInput.value)) return;
                            messageInput.value = '';
                            messageInput.focus();
                            updateBtn();
                            scrollToBottom();
                        };
                    }
                }

                // Handle scrolling in messages area for loading more messages
                function handleScrolling(){
                    let messagesArea = document.getElementById("messages-area");
                    let loadMoreIndicator = document.getElementById("load-more-indicator");
                    messagesArea.addEventListener("scroll", () => {
                        if (messagesArea.scrollTop == 0) {
                            loadMoreIndicator.classList.remove("hidden");
                            setTimeout(() => {
                                // TODO: Livewire event to load more messages
                            }, 20);
                        }
                    });
                }

                // Handle loading more messages to maintain scroll position
                function handleLoadingMore(event) {
                    let messagesArea = document.getElementById("messages-area");
                    let loadMoreIndicator = document.getElementById("load-more-indicator");
                    if (messagesArea) {
                        setTimeout(() => {
                            const previousHeight = event.detail.height;
                            const newHeight = messagesArea.scrollHeight;
                            requestAnimationFrame(() => {
                                messagesArea.scrollTo({
                                    top: newHeight - previousHeight,
                                    behavior: "auto",
                                });
                            });
                            loadMoreIndicator.classList.add("hidden");
                            // TODO: Livewire event to reset height state
                        }, 20);
                    }
                }

                // Function to scroll to bottom of messages
                function scrollToBottom() {
                    let messagesArea = document.getElementById("messages-area");
                    if (messagesArea) {
                        // Use requestAnimationFrame to ensure the DOM has been updated
                        requestAnimationFrame(() => {
                            messagesArea.scrollTo({
                                top: messagesArea.scrollHeight,
                                behavior: "smooth",
                            });
                        });
                    }
                }

                // Handle back button
                function handleBackButton() {
                    let backButton = document.getElementById("backButton");
                    let chatArea = document.getElementById("chat-area");
                    let leftSidebar = document.getElementById("left-sidebar");
                    backButton.addEventListener("click", () => {
                        if (window.innerWidth < 768) {
                            leftSidebar.style.display = "flex";
                            chatArea.style.display = "none";
                        }
                    });
                }

                // Handle chat selection 
                function handleChatSelection(event) {
                    let chatArea = document.getElementById("chat-area");
                    let leftSidebar = document.getElementById("left-sidebar");
                    if (window.innerWidth < 768) {
                        leftSidebar.style.display = "none";
                        chatArea.style.display = "flex";
                    }

                    setTimeout(() => {
                        let messagesArea = document.getElementById("messages-area");
                        let messageInput = document.getElementById("message-input");
                        if (messagesArea && messageInput) {
                            const height = messagesArea.scrollHeight;
                            messagesArea.scrollTop = height;

                            handleInput();
                            handleScrolling();
                            handleBackButton();
                            // TODO: Livewire event to set height state
                        }
                    }, 50);
                }
            });
