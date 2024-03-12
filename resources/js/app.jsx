import './bootstrap';

import React from "react";
import { BrowserRouter, Routes, Route } from "react-router-dom";

const App = () => {
    return (
        <>
            <BrowserRouter>
                <Routes>
                        {/* <Route exact path="/" element={<Posts />}></Route> */}
                        {/* <Route
                            exact
                            path="/create-post"
                            element={<CreatePost />}
                        ></Route> */}
                    <Route path="*" element={<h1>404 Not found</h1>}></Route>
                </Routes>
            </BrowserRouter>
        </>
    );
};

export default App;